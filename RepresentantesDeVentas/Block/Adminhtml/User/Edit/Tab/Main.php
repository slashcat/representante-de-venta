<?php
namespace Formax\RepresentantesDeVentas\Block\Adminhtml\User\Edit\Tab;

use Magento\Backend\Block\Widget\Form;

class Main extends \Magento\User\Block\User\Edit\Tab\Main
{
    /**
     * Prepare form fields
     *
     * @return Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $model = $this->_coreRegistry->registry('permissions_user');
        $baseFieldset = $form->getElement('base_fieldset');
        $baseFieldset->addField(
            'categorias_asociadas',
            'text',
            [
                'name' => 'categorias_asociadas',
                'label' => __('Categorías Asociadas'),
                'title' => __('Categorías Asociadas'),
                'value' => $model->getCategoriasAsociadas()
            ]
        );
        $baseFieldset->addField(
            'telefono',
            'text',
            [
                'name' => 'telefono',
                'label' => __('Teléfono'),
                'title' => __('Teléfono'),
                'value' => $model->getTelefono()
            ]
        );
        return $this;
    }
}
