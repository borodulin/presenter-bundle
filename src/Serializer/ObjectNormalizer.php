<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Serializer;

use Borodulin\PresenterBundle\DoctrineInteraction\MetadataRegistry;
use Borodulin\PresenterBundle\Presenter\Presenter;
use Borodulin\PresenterBundle\PresenterContext\ObjectContext;
use Borodulin\PresenterBundle\PresenterContext\ObjectContextFactory;
use Borodulin\PresenterBundle\PresenterHandler\PresenterHandlerRegistry;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ObjectNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface $normalizer;
    private NameConverterInterface $nameConverter;

    public function __construct(
        private readonly PresenterHandlerRegistry $presenterHandlerRegistry,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly PropertyListExtractorInterface $propertyListExtractor,
        private readonly MetadataRegistry $metadataRegistry,
        private readonly ObjectContextFactory $objectContextFactory,
        NameConverterInterface $nameConverter = null
    ) {
        $this->nameConverter = $nameConverter ?? new DummyNameConverter();
    }

    public function normalize($object, string $format = null, array $context = []): \ArrayObject|array
    {
        if ($object instanceof Presenter) {
            $objectContext = $object->objectContext;
            $object = $object->getObject();
        } else {
            $objectContext = $this->objectContextFactory->createFromArrayContext($context);
        }

        $result = $this->expand(
            $object,
            $objectContext,
            $format,
        );

        return \count($result) ? $result : new \ArrayObject();
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        if (\is_object($data)) {
            $class = $data::class;

            return null !== $this->presenterHandlerRegistry->hasPresenterHandlerForClass($class)
                || null !== $this->metadataRegistry->getMetadataForClass($class);
        }

        return false;
    }

    public function expand(
        object $object,
        ObjectContext $context,
        string $format = null
    ): array {
        $data = [];

        $nameConverter = $context->nameConverter ?? $this->nameConverter;
        $expand = $context->expandRequest?->getExpand() ?? [];

        $class = $object::class;

        [$presenterHandler, $method] = $this->presenterHandlerRegistry->getPresenterHandlerForClass($class, $context->group);
        $metaData = $this->metadataRegistry->getMetadataForClass($class);

        if (\is_callable([$presenterHandler, $method])) {
            $presented = \call_user_func([$presenterHandler, $method], $object, $context);
        } elseif (null !== $metaData) {
            $presented = [];
            foreach ($metaData->getFieldNames() as $fieldName) {
                if ($this->propertyAccessor->isReadable($object, $fieldName)) {
                    $presented[$fieldName] = $this->propertyAccessor->getValue($object, $fieldName);
                }
            }
        } else {
            $presented = $object;
        }

        if (\is_object($presented)) {
            if ($class !== $presented::class) {
                $presented = $this->normalizer->normalize($presented, $format, $context->toArray());
            }
        }

        if (\is_object($presented)) {
            foreach ($this->propertyListExtractor->getProperties($presented::class) as $property) {
                if ($this->propertyAccessor->isReadable($presented, $property)) {
                    $data[$property] = $this->propertyAccessor->getValue($presented, $property);
                }
            }
        } elseif (\is_array($presented)) {
            $data = $presented;
        }

        $result = [];
        foreach ($data as $name => $value) {
            $result[$nameConverter->normalize($name)] = $value;
        }

        $metaData = $this->metadataRegistry->getMetadataForClass($class);
        $customExpandFields = $this->presenterHandlerRegistry->getCustomExpandFieldsForClass($class, $context->group);

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
        $expand = array_filter($expand, fn ($item) => !str_contains($item, '*'));
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
                                    fn ($association) => $this->expand($association, $context, $format),
                                    $value
                                );
                            } else {
                                $result[$expandName] = $this->expand($value, $context, $format);
                            }
                        } else {
                            $result[$expandName] = $this->normalizer->normalize($value, $format, $context->toArray());
                        }
                    }
                } elseif (\is_callable($expandableField)) {
                    $value = \call_user_func($expandableField, $object, $context);
                    if (\is_object($value)) {
                        if ($value instanceof Collection) {
                            $result[$expandName] = array_map(
                                fn ($association) => $this->expand($association, $context, $format),
                                $value->toArray()
                            );
                        } else {
                            $result[$expandName] = $this->expand($value, $context, $format);
                        }
                    } else {
                        $result[$expandName] = $this->normalizer->normalize($value, $format, $context->toArray());
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
