<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Serializer;

use Borodulin\PresenterBundle\DoctrineInteraction\MetadataRegistry;
use Borodulin\PresenterBundle\PresenterHandler\PresenterHandlerRegistry;
use Borodulin\PresenterBundle\Request\Expand\ExpandRequestInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PresenterNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private PresenterHandlerRegistry $entityConverterRegistry;
    private PropertyAccessorInterface $propertyAccessor;
    private PropertyListExtractorInterface $propertyListExtractor;
    private MetadataRegistry $metadataRegistry;
    private NormalizerInterface $normalizer;
    private NameConverterInterface $nameConverter;

    public function __construct(
        PresenterHandlerRegistry $entityConverterRegistry,
        PropertyAccessorInterface $propertyAccessor,
        PropertyListExtractorInterface $propertyListExtractor,
        MetadataRegistry $metadataRegistry,
        ?NameConverterInterface $nameConverter = null
    ) {
        $this->entityConverterRegistry = $entityConverterRegistry;
        $this->propertyAccessor = $propertyAccessor;
        $this->propertyListExtractor = $propertyListExtractor;
        $this->metadataRegistry = $metadataRegistry;
        $this->nameConverter = $nameConverter ?? new DummyNameConverter();
    }

    /**
     * @return array|\ArrayObject
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $result = $this->expand(
            $object,
            $format,
            $context
        );

        return \count($result) ? $result : new \ArrayObject();
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        if (\is_object($data)) {
            $class = \get_class($data);

            return null !== $this->entityConverterRegistry->getPresenterHandlerForClass($class)
                || null !== $this->metadataRegistry->getMetadataForClass($class);
        }

        return false;
    }

    public function expand(
        object $object,
        string $format = null,
        array $context = []
    ): array {
        $expandRequest = $context['expand_request'] ?? null;
        $expand = $expandRequest instanceof ExpandRequestInterface ? $expandRequest->getExpand() : [];
        $nameConverter = $context['name_converter'] ?? null;
        $nameConverter = $nameConverter instanceof NameConverterInterface ?: $this->nameConverter;

        $data = [];

        $class = \get_class($object);

        $converter = $this->entityConverterRegistry->getPresenterHandlerForClass($class);
        $metaData = $this->metadataRegistry->getMetadataForClass($class);

        if (null !== $converter && \is_callable($converter)) {
            $converted = \call_user_func($converter, $object, $context);
        } elseif (null !== $metaData) {
            $converted = [];
            foreach ($metaData->getFieldNames() as $fieldName) {
                if ($this->propertyAccessor->isReadable($object, $fieldName)) {
                    $converted[$fieldName] = $this->propertyAccessor->getValue($object, $fieldName);
                }
            }
        } else {
            $converted = $object;
        }

        if (\is_object($converted)) {
            if ($class !== \get_class($converted)) {
                $converted = $this->normalizer->normalize($converted, $format, $context);
            }
        }

        if (\is_object($converted)) {
            foreach ($this->propertyListExtractor->getProperties(\get_class($converted)) as $property) {
                if ($this->propertyAccessor->isReadable($converted, $property)) {
                    $data[$property] = $this->propertyAccessor->getValue($converted, $property);
                }
            }
        } elseif (\is_array($converted)) {
            $data = $converted;
        }

        $result = [];
        foreach ($data as $name => $value) {
            $result[$nameConverter->normalize($name)] = $value;
        }

        $class = \get_class($object);
        $metaData = $this->metadataRegistry->getMetadataForClass($class);
        $customExpandFields = $this->entityConverterRegistry->getCustomExpandFieldsForClass($class);

        $expandable = [];
        if (null !== $customExpandFields) {
            $expandable = $customExpandFields->getExpandFields();
        } elseif (null !== $metaData) {
            $expandable = $metaData->getAssociationNames();
        }
        if (!\count($expandable)) {
            return $result;
        }
        $expandableNormalized = [];
        foreach ($expandable as $key => $value) {
            if (\is_string($key) && (\is_string($value) || \is_callable($value))) {
                $expandableNormalized[$nameConverter->normalize($key)] = $value;
            } elseif (\is_int($key) && \is_string($value)) {
                $expandableNormalized[$nameConverter->normalize($value)] = $value;
            }
        }
        $expandTree = [];
        if (\in_array('*', $expand)) {
            foreach ($expandableNormalized as $key => $value) {
                $expandTree[$key] = [];
            }
        }
        $expand = array_filter($expand, fn ($item) => false === strpos($item, '*'));
        foreach ($expand as $expandItem) {
            $normalizedNames = array_map(
                fn ($item) => $nameConverter->normalize($item),
                explode('.', $expandItem)
            );
            $normalizedName = array_shift($normalizedNames);
            if (!\array_key_exists($normalizedName, $expandableNormalized)) {
                continue;
            }
            if (\count($normalizedNames)) {
                $nestedExpand = implode('.', $normalizedNames);
                $expandTree[$normalizedName][$nestedExpand] = $nestedExpand;
            } else {
                $expandTree[$normalizedName] = [];
            }
        }

        foreach ($expandTree as $expandName => $nestedExpand) {
            $context['expand'] = array_values($nestedExpand);
            if (\array_key_exists($expandName, $expandableNormalized)) {
                $expandableField = $expandableNormalized[$expandName];
                if (\is_string($expandableField)) {
                    if ($this->propertyAccessor->isReadable($object, $expandableField)) {
                        $value = $this->propertyAccessor->getValue($object, $expandableField);
                        if (null !== $metaData && $metaData->hasAssociation($expandableField)) {
                            $multiple = $metaData->isCollectionValuedAssociation($expandableField);
                            if (null === $value) {
                                $result[$expandName] = null;
                            } elseif ($multiple) {
                                if ($value instanceof Collection) {
                                    $value = $value->toArray();
                                }
                                $result[$expandName] = array_map(
                                    fn ($association) => $this->expand($association, $format, $context),
                                    $value
                                );
                            } else {
                                $result[$expandName] = $this->expand($value, $format, $context);
                            }
                        } else {
                            $result[$expandName] = $this->normalizer->normalize($value, $format, $context);
                        }
                    }
                } elseif (\is_callable($expandableField)) {
                    $value = \call_user_func($expandableField, $object, $context);
                    if (\is_object($value)) {
                        if ($value instanceof Collection) {
                            $result[$expandName] = array_map(
                                fn ($association) => $this->expand($association, $format, $context),
                                $value->toArray()
                            );
                        } else {
                            $result[$expandName] = $this->expand($value, $format, $context);
                        }
                    } else {
                        $result[$expandName] = $this->normalizer->normalize($value, $format, $context);
                    }
                }
            }
        }

        return $result;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->normalizer = $serializer;
    }
}
