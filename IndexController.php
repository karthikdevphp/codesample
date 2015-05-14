<?php

 class IndexController extends ApplicationController
 {
    public function index()
    {
        header( 'Location: /lesson-planner/lesson/create/' );
        return;
    }
 }
 
 ?>
