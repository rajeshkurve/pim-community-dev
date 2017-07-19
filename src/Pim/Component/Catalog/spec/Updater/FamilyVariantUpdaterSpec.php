<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Updater\FamilyVariantUpdater;
use Prophecy\Argument;

class FamilyVariantUpdaterSpec extends ObjectBehavior
{
    function let(
        SimpleFactoryInterface $attributeSetFactory,
        TranslatableUpdater $updater,
        IdentifiableObjectRepositoryInterface $familyRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($attributeSetFactory, $updater, $familyRepository, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_new_family_variant(
        $updater,
        $attributeSetFactory,
        $familyRepository,
        $attributeRepository,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        VariantAttributeSetInterface $attributeSet1,
        VariantAttributeSetInterface $attributeSet2,
        VariantAttributeSetInterface $commonAttributeSet,
        AttributeInterface $name,
        AttributeInterface $size,
        AttributeInterface $color,
        AttributeInterface $description,
        AttributeInterface $sku,
        AttributeInterface $other
    ) {
        $familyRepository->findOneByIdentifier('t-shirt')->willReturn($family);

        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(
            ['name', 'size', 'description', 'color', 'sku']
        );

        $attributeRepository->findOneByIdentifier('name')->willreturn($name);
        $attributeRepository->findOneByIdentifier('size')->willreturn($size);
        $attributeRepository->findOneByIdentifier('color')->willreturn($color);
        $attributeRepository->findOneByIdentifier('description')->willreturn($description);
        $attributeRepository->findOneByIdentifier('sku')->willreturn($sku);
        $attributeRepository->findOneByIdentifier('other')->willreturn($other);

        $familyVariant->setCode('my-tshirt')->shouldBeCalled();
        $familyVariant->setFamily($family)->shouldBeCalled();
        $familyVariant->getVariantAttributeSet(Argument::any())->willReturn(null);
        $updater->update($familyVariant, ['en_US' => 'My tshirt'])->shouldBeCalled();

        $attributeSetFactory->create()->willReturn($attributeSet1, $attributeSet2, $commonAttributeSet);

        $familyVariant->addVariantAttributeSet($attributeSet1)->shouldBeCalled();
        $attributeSet1->setAxes([$color])->shouldBeCalled();
        $attributeSet1->setAttributes([$description])->shouldBeCalled();
        $attributeSet1->setLevel(1)->shouldBeCalled();

        $familyVariant->addVariantAttributeSet($attributeSet2)->shouldBeCalled();
        $attributeSet2->setAxes([$size, $other])->shouldBeCalled();
        $attributeSet2->setAttributes([$size, $sku])->shouldBeCalled();
        $attributeSet2->setLevel(2)->shouldBeCalled();

        $this->update($familyVariant, [
            'code' => 'my-tshirt',
            'family' => 't-shirt',
            'labels' => [
                'en_US' => 'My tshirt'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['description'],
                    'level' => 1,
                ],
                [
                    'axes' => ['size', 'other'],
                    'attributes' => ['size', 'sku'],
                    'level' => 2,
                ]
            ],
        ], []);
    }

    function it_updates_an_existing_family_variant(
        $updater,
        $attributeSetFactory,
        $familyRepository,
        $attributeRepository,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        VariantAttributeSetInterface $attributeSet1,
        VariantAttributeSetInterface $attributeSet2,
        VariantAttributeSetInterface $commonAttributeSet,
        AttributeInterface $name,
        AttributeInterface $size,
        AttributeInterface $color,
        AttributeInterface $description,
        AttributeInterface $sku,
        AttributeInterface $other
    ) {
        $familyRepository->findOneByIdentifier('t-shirt')->willReturn($family);

        $familyVariant->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(
            ['name', 'size', 'description', 'color', 'sku']
        );

        $attributeRepository->findOneByIdentifier('name')->willreturn($name);
        $attributeRepository->findOneByIdentifier('size')->willreturn($size);
        $attributeRepository->findOneByIdentifier('color')->willreturn($color);
        $attributeRepository->findOneByIdentifier('description')->willreturn($description);
        $attributeRepository->findOneByIdentifier('sku')->willreturn($sku);
        $attributeRepository->findOneByIdentifier('other')->willreturn($other);

        $familyVariant->setCode('my-tshirt')->shouldBeCalled();
        $familyVariant->setFamily($family)->shouldBeCalled();
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet1);
        $familyVariant->getVariantAttributeSet(2)->willReturn($attributeSet2);
        $updater->update($familyVariant, ['en_US' => 'My tshirt'])->shouldBeCalled();

        $attributeSetFactory->create()->willReturn($attributeSet1, $attributeSet2, $commonAttributeSet);

        $familyVariant->addVariantAttributeSet(Argument::any())->shouldNotBeCalled();
        $attributeSet1->setAxes([$color])->shouldBeCalled();
        $attributeSet1->setAttributes([$description])->shouldBeCalled();
        $attributeSet1->setLevel(Argument::any())->shouldNotBeCalled();

        $attributeSet2->setAxes([$size, $other])->shouldBeCalled();
        $attributeSet2->setAttributes([$size, $sku])->shouldBeCalled();
        $attributeSet2->setLevel(Argument::any())->shouldNotBeCalled();

        $this->update($familyVariant, [
            'code' => 'my-tshirt',
            'family' => 't-shirt',
            'labels' => [
                'en_US' => 'My tshirt'
            ],
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => ['description'],
                    'level' => 1,
                ],
                [
                    'axes' => ['size', 'other'],
                    'attributes' => ['size', 'sku'],
                    'level' => 2,
                ]
            ],
        ], []);
    }

    function it_throws_an_exception_if_the_family_code_is_invalid(
        $familyRepository,
        FamilyVariantInterface $familyVariant
    ) {
        $familyRepository->findOneByIdentifier('t-shirt')->willReturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [
            $familyVariant,
            [
                'family' => 't-shirt',
            ],
        ]);
    }

    function it_throws_an_exception_if_code_is_not_string(FamilyVariantInterface $familyVariant)
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('update', [
            $familyVariant,
            [
                'code' => null,
            ],
        ]);
    }

    function it_throws_an_exception_if_labels_are_not_an_array(FamilyVariantInterface $familyVariant)
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('update', [
            $familyVariant,
            [
                'labels' => null,
            ],
        ]);
    }

    function it_throws_an_exception_if_variant_attribute_sets_are_not_an_array(FamilyVariantInterface $familyVariant)
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('update', [
            $familyVariant,
            [
                'variant_attribute_sets' => null,
            ],
        ]);
    }

    function it_throws_an_exception_if_the_given_object_is_not_a_family_variant(ChannelInterface $channel)
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [$channel, []]);
    }
}
