<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;

class AddToGroupsSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        GroupRepository $groupRepository,
        Group $shirts,
        Group $pants
    ) {
        $productRepository->implement('Doctrine\Common\Persistence\ObjectRepository');
        $this->beConstructedWith($productRepository, $groupRepository);
    }

    function it_is_a_mass_edit_action()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionInterface');
    }

    function it_stores_groups_to_add_the_products_to($shirts, $pants)
    {
        $groups = $this->getGroups();
        $groups->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $groups->shouldBeEmpty();

        $this->setGroups([$shirts, $pants]);

        $groups = $this->getGroups();
        $groups->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');

        $groups->shouldHaveCount(2);
        $groups->first()->shouldReturn($shirts);
        $groups->last()->shouldReturn($pants);
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_add_to_groups');
    }

    function it_provides_form_options($groupRepository, $shirts, $pants)
    {
        $groupRepository->findAll()->willReturn([$shirts, $pants]);

        $this->getFormOptions()->shouldReturn(['groups' => [$shirts, $pants]]);
    }

    function it_adds_products_to_groups_when_performimg_the_operation(
        $productRepository,
        AbstractQuery $query,
        ProductInterface $product1,
        ProductInterface $product2,
        $shirts,
        $pants
    ) {
        $this->setProductsToMassEdit([$product1, $product2]);

        $this->setGroups([$shirts, $pants]);

        $shirts->addProduct($product1)->shouldBeCalled();
        $shirts->addProduct($product2)->shouldBeCalled();
        $pants->addProduct($product1)->shouldBeCalled();
        $pants->addProduct($product2)->shouldBeCalled();

        $this->perform();
    }
}
