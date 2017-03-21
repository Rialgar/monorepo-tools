<?php

namespace Shopsys\ShopBundle\Form\Admin\Module;

use Shopsys\ShopBundle\Form\YesNoType;
use Shopsys\ShopBundle\Model\Module\ModuleList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModulesFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('modules', FormType::class)
            ->add('save', SubmitType::class);

        $moduleList = $options['module_list'];
        /** @var \Shopsys\ShopBundle\Model\Module\ModuleList $moduleList */
        foreach ($moduleList->getModuleNamesIndexedByLabel() as $moduleLabel => $moduleName) {
            $builder->get('modules')
                ->add($moduleName, YesNoType::class, [
                    'label' => $moduleLabel,
                ]);
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('module_list')
            ->setAllowedTypes('module_list', ModuleList::class)
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}