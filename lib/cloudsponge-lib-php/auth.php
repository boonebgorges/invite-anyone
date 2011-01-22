<?php 

// CloudSponge.com PHP Library v0.9.2 Beta
// http://www.cloudsponge.com
// Copyright (c) 2010 Cloud Copy, Inc.
// Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
//
// Written by Graeme Rouse
// graeme@cloudsponge.com

require_once 'csimport.php';
echo CSImport::forward_auth($_GET, $_POST);
?>