<?php
namespace classicprison\mines\store;


use classicprison\mines\MineReset;

class EntityStore{
    private $store;
    /** @var  MineReset */
    private $api;

    /**
     * EntityStore constructor.
     * @param MineReset $api
     */
    public function __construct(MineReset $api){
        $this->api = $api;
        $this->store = [];
    }

    public function storeEntities($mineName, $entities){
        $this->store[$mineName] = $entities;
    }

    public function retrieveEntities($mineName){
        if(isset($this->store[$mineName])){
            $entities = $this->store[$mineName];
            unset($this->store[$mineName]);
            return $entities;
        }
        return null;
    }

}