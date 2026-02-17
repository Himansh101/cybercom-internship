<?php

class Core_Controllers_Front_Action
{
    public function getRequest()
    {
        return Sdp::getModel("core/request");
    }
}