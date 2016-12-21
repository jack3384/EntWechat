<?php

namespace glacier\EntWechat;

interface MsgHandlerInterface
{
    public function getMsgArray();
    public function responseMsg(array $msg);
}