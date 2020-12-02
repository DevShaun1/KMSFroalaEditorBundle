<?php

declare(strict_types=1);

namespace KMS\FroalaEditorBundle\Tests\Form\Type;

use KMS\FroalaEditorBundle\DependencyInjection\Configuration;
use KMS\FroalaEditorBundle\Form\Type\FroalaEditorType;
use KMS\FroalaEditorBundle\Service\OptionManager;
use KMS\FroalaEditorBundle\Service\PluginProvider;
use KMS\FroalaEditorBundle\Utility\UConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\Routing\RouterInterface;

final class FroalaEditorTypeTest extends TestCase
{
    public function testConfiguration(): void
    {
        $form = $this->getForm();
        $view = $form->createView();

        static::assertSame('en', $view->vars['froala_arrOption']['language']);
    }

    public function testValidProfile(): void
    {
        $form = $this->getForm(['froala_profile' => 'profile1']);
        $view = $form->createView();

        static::assertSame('fr', $view->vars['froala_arrOption']['language']);
    }

    public function testNonExistentProfile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $form = $this->getForm(['froala_profile' => 'profile2']);
        $form->createView();
    }

    public function testValidOption(): void
    {
        $form = $this->getForm(['froala_fontAwesomeSets' => ['first' => 'test']]);
        $view = $form->createView();

        static::assertArrayHasKey('fontAwesomeSets', $view->vars['froala_arrOption']);
        static::assertSame(['first' => 'test'], $view->vars['froala_arrOption']['fontAwesomeSets']);
    }

    public function testInvalidOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        $this->getForm(['froala_unknown_option' => 'test']);
    }

    private function getForm(array $options = []): FormInterface
    {
        $router = $this->getMockBuilder(RouterInterface::class)->getMock();
        $formType = new FroalaEditorType(
            $this->getParameterBag(),
            new OptionManager($router),
            new PluginProvider()
        );

        $factory = Forms::createFormFactoryBuilder()
            ->addType($formType)
            ->getFormFactory();

        return $factory->create(FroalaEditorType::class, null, $options);
    }

    private function getParameterBag(): ParameterBag
    {
        $array = array_merge(
            UConfiguration::$OPTIONS_STRING, UConfiguration::$OPTIONS_STRING_CUSTOM,
            UConfiguration::$OPTIONS_BOOLEAN, UConfiguration::$OPTIONS_BOOLEAN_CUSTOM,
            UConfiguration::$OPTIONS_ARRAY, UConfiguration::$OPTIONS_ARRAY_CUSTOM,
            UConfiguration::$OPTIONS_OBJECT, UConfiguration::$OPTIONS_OBJECT_CUSTOM,
            UConfiguration::$OPTIONS_INTEGER
        );
        $parameters = [];

        foreach ($array as $option => $defaultValue) {
            $parameters[Configuration::$NODE_ROOT . '.' . $option] = $defaultValue;
        }

        // Define some options for testing
        $parameters[Configuration::$NODE_ROOT . '.' . 'language'] = 'en';
        $parameters[Configuration::$NODE_ROOT . '.' . 'profiles'] = ['profile1' => ['language' => 'fr']];

        return new ParameterBag($parameters);
    }
}
