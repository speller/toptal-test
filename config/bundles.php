<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\WebServerBundle\WebServerBundle::class => ['dev' => true, 'dev_local' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
];
