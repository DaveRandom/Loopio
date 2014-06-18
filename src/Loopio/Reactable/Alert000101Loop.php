<?php

namespace Loopio\Reactable;

abstract class Alert000101Loop extends Loop
{
    /**
     * Scale factor by which times are adjusted
     *
     * Alert >=0.1.1, <0.6.0
     *
     * @var int
     */
    protected $timeScaleFactor = 1;
}
