<?php
namespace GroceryCrud\Core\State;

use GroceryCrud\Core\GroceryCrud as GCrud;
use GroceryCrud\Core\Render\RenderAbstract;
use GroceryCrud\Core\Error\ErrorMessageInteface;

class CloneState extends StateAbstract {

    /**
     * MainState constructor.
     * @param GCrud $gCrud
     */
    function __construct(GCrud $gCrud)
    {
        $this->gCrud = $gCrud;
    }

    public function getStateParameters()
    {
        $data = $_POST;

        $data['data'] = $this->filterData($data['data']);

        return (object)array(
            'data' => $data['data']
        );
    }

    public function render()
    {
        if (!$this->gCrud->getLoadClone()) {
            throw new \Exception('Permission denied. You are not allowed to use this operation');
        }

        $stateParameters = $this->getStateParameters();

        $this->setInitialData();
        $model = $this->gCrud->getModel();

        if ($this->gCrud->getWhere() !== null) {
            $model->setWhere($this->gCrud->getWhere());
        }

        $validator = $this->setValidationRules('clone');

        $output = (object)array();

        if (!$validator->validate()) {
            $output->status = 'error';
            $output->errors = $validator->getErrors();
        } else {

            $fieldTypes = $this->getFieldTypes();

            foreach ($stateParameters->data as $field_name => $field_value) {
                if ($field_value === '' && !empty($fieldTypes->$field_name) && $fieldTypes->$field_name->isNullable) {
                    $stateParameters->data[$field_name] = null;
                }
            }

            $cloneFields = $this->getcloneFields();
            $readOnlyCloneFields = $this->gCrud->getreadOnlyCloneFields();

            $relationNtoNData = $this->getRelationNtoNData($cloneFields, $stateParameters->data);
            $stateParameters->data = $this->getFilteredData($cloneFields, $stateParameters->data, $readOnlyCloneFields);

            $uploadedBlobData = $this->uploadBlobData($fieldTypes);
            if (!empty($uploadedBlobData)) {
                $stateParameters->data = $this->addUploadBlobData($stateParameters->data, $uploadedBlobData);
            }

            $uploadedData = $this->uploadOneData($fieldTypes);
            if (!empty($uploadedData)) {
                $stateParameters->data = $this->addUploadOneData($stateParameters->data, $uploadedData);
            }

            $uploadedMultipleData = $this->uploadMultipleData($fieldTypes);
            if (!empty($uploadedMultipleData)) {
                $stateParameters->data = $this->addUploadMultipleData($stateParameters->data, $uploadedMultipleData);
            }

            $operationResponse = $this->stateOperationWithCallbacks($stateParameters, 'Insert', function ($stateParameters) use ($relationNtoNData) {
                $model = $this->gCrud->getModel();
                $insertResult = $model->insert($stateParameters->data);
                $stateParameters->insertId = $insertResult->insertId;

                $relationNtoNfields = $this->gCrud->getRelationNtoN();

                foreach ($relationNtoNData as $fieldName => $relationData) {
                    $model->insertRelationManytoMany($relationNtoNfields[$fieldName], $relationData, $stateParameters->insertId);
                }

                return $stateParameters;
            });

            $output = $this->setResponseStatusAndMessage($output, $operationResponse);

            if ($operationResponse !== false && !($operationResponse instanceof ErrorMessageInteface)) {
                $output->insertId = $operationResponse->insertId;
            }
        }

        $output = $this->addcsrfToken($output);

        $render = new RenderAbstract();

        $render->output = json_encode($output);
        $render->outputAsObject = $output;
        $render->isJSONResponse = true;

        return $render;

    }
}