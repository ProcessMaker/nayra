<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Data store interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface DataStoreInterface extends ItemAwareElementInterface
{

    /**
     * Type of element.
     */
    const TYPE = 'bpmnDataStore';


    /**
     * Properties.
     */
    const PROPERTIES = [
        'APP_UID' => '',
        'APP_TITLE' => null,
        'APP_DESCRIPTION' => null,
        'APP_NUMBER' => '0',
        'APP_PARENT' => '0',
        'APP_STATUS' => '',
        'APP_STATUS_ID' => '0',
        'PRO_UID' => '',
        'APP_PROC_STATUS' => '',
        'APP_PROC_CODE' => '',
        'APP_PARALLEL' => 'NO',
        'APP_INIT_USER' => '',
        'APP_CUR_USER' => '',
        'APP_CREATE_DATE' => null,
        'APP_INIT_DATE' => null,
        'APP_FINISH_DATE' => null,
        'APP_UPDATE_DATE' => null,
        'APP_DATA' => null,
        'APP_PIN' => '',
        'APP_DURATION' => '0',
        'APP_DELAY_DURATION' => '0',
        'APP_DRIVE_FOLDER_UID' => '',
        'APP_ROUTING_DATA' => null
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [

    ];

    /**
     * Get data from store.
     *
     * @param $name
     * @param $default
     *
     * @return mixed
     */
    public function getData($name = null, $default = null);

    /**
     * Set data of the store.
     *
     * @param $data
     *
     * @return $this
     */
    public function setData($data);

    /**
     * Put data to store.
     *
     * @param $name
     * @param $data
     *
     * @return $this
     */
    public function putData($name, $data);
}
