<?php

/*
 * This file is part of the Limenius\Liform package.
 *
 * (c) Limenius <https://github.com/Limenius/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limenius\Liform\Transformer;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Nacho Martín <nacho@limenius.com>
 */
abstract class AbstractTransformer implements TransformerInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var FormTypeGuesserInterface|null
     */
    protected $validatorGuesser;

    /**
     * @param TranslatorInterface           $translator
     * @param FormTypeGuesserInterface|null $validatorGuesser
     */
    public function __construct(TranslatorInterface $translator, FormTypeGuesserInterface $validatorGuesser = null)
    {
        $this->translator = $translator;
        $this->validatorGuesser = $validatorGuesser;
    }

    /**
     * @param ExtensionInterface[] $extensions
     * @param FormInterface        $form
     * @param array                $schema
     *
     * @return array
     */
    protected function applyExtensions(array $extensions, FormInterface $form, array $schema)
    {
        $newSchema = $schema;
        foreach ($extensions as $extension) {
            $newSchema = $extension->apply($form, $newSchema);
        }

        return $newSchema;
    }

    /**
     * @param FormInterface        $form
     * @param array                $schema
     * @param ExtensionInterface[] $extensions
     * @param string               $widget
     *
     * @return array
     */
    protected function addCommonSpecs(FormInterface $form, array $schema, $extensions = [], $widget)
    {
        //$schema = $this->addLabel($form, $schema);
        $schema = $this->addType($form, $schema);
        $schema = $this->addMetaData($form, $schema);
        $schema = $this->addViewData($form, $schema);
        $schema = $this->addAttr($form, $schema);
        $schema = $this->addPattern($form, $schema);
        $schema = $this->addDescription($form, $schema);
        $schema = $this->addWidget($form, $schema, $widget);
        $schema = $this->addDependencies($form, $schema);
        $schema = $this->applyExtensions($extensions, $form, $schema);

        return $schema;
    }


    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addPattern(FormInterface $form, array $schema)
    {
        if ($attr = $form->getConfig()->getOption('attr')) {
            if (isset($attr['pattern'])) {
                $schema['pattern'] = $attr['pattern'];
            }
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addLabel(FormInterface $form, array $schema)
    {
        /*$translationDomain = $form->getConfig()->getOption('translation_domain');
        if ($label = $form->getConfig()->getOption('label')) {
            $schema['title'] = $label;//$this->translator->trans($label, [], $translationDomain);
        } else {
            $schema['title'] = $form->getName();//$this->translator->trans($form->getName(), [], $translationDomain);
        }*/

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addAttr(FormInterface $form, array $schema)
    {
        if ($attr = $form->getConfig()->getOption('attr')) {
            $schema['attr'] = $attr;
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addDescription(FormInterface $form, array $schema)
    {
        if ($liform = $form->getConfig()->getOption('ui-schema')) {
            if (isset($liform['description']) && $description = $liform['description']) {
                $schema['description'] = $this->translator->trans($description);
            }
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     * @param mixed         $configWidget
     *
     * @return array
     */
    protected function addViewData(FormInterface $form, array $schema)
    {
        if ($liform = $form->getConfig()->getOption('ui-schema')) {
            if (isset($liform['view'])) {
                $schema['view'] = $liform['view'];
            }
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     * @param mixed         $configWidget
     *
     * @return array
     */
    protected function addType(FormInterface $form, array $schema)
    {
        if ($liform = $form->getConfig()->getOption('ui-schema')) {
            if (isset($liform['type'])) {
                $schema['type'] = $liform['type'];
            }
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     * @param mixed         $configWidget
     *
     * @return array
     */
    protected function addMetaData(FormInterface $form, array $schema)
    {
        if ($this->isDisabled($form) === true) {
            $schema['readOnly'] = true;
        }

        if ($liform = $form->getConfig()->getOption('ui-schema')) {
            if (isset($liform['description'])) {
                $schema['description'] = $liform['description'];
            }
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     * @param mixed         $configWidget
     *
     * @return array
     */
    protected function addWidget(FormInterface $form, array $schema, $configWidget)
    {
        if ($liform = $form->getConfig()->getOption('ui-schema')) {
            if (isset($liform['widget']) && $widget = $liform['widget']) {
                $schema['widget'] = $widget;
            }
        } elseif ($configWidget) {
            $schema['widget'] = $configWidget;
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     * @param mixed         $configWidget
     *
     * @return array
     */
    protected function addDependencies(FormInterface $form, array $schema)
    {
        if ($liform = $form->getConfig()->getOption('ui-schema')) {
            if (isset($liform['dependencies']) && $dependencies = $liform['dependencies']) {
                $schema['dependencies'] = $dependencies;
            }
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     *
     * @return boolean
     */
    protected function isRequired(FormInterface $form)
    {
        return $form->getConfig()->getOption('required');
    }

    /**
     * @param FormInterface $form
     *
     * @return boolean
     */
    protected function isDisabled(FormInterface $form)
    {
        return $form->getConfig()->getOption('disabled');
    }
}
