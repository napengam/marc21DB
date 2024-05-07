<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of isbdElements
 *
 * @author Heinz
 */
class isbdElements {

    private $tm;

    function __construct($tm) {
        $this->tm = $tm;
    }

    function title() {
        $tia = [];
        $tia[] = $this->tm->getData('245', 1, 'a');
        $tia[] = $this->tm->getData('245', 1, 'b');
        $ti = implode(' / ', array_filter($tia));
        return $ti;
    }

    function author() {
        $au = $this->tm->getData('100', 1, 'a');
        $au .= " " . $this->tm->getData('100', 1, 'd');
        $au .= " " . $this->tm->getData('100', 1, 'e');
        return trim($au);
    }

    function isbn() {
        return $this->tm->getData('020', 1, '9');
    }

    function price() {
        return $this->tm->getData('020', 1, 'c');
    }

    function ddc() {
        $tm = $this->tm;
        $ddc = '';
        $q = $tm->getData('082', 1, 'q');
        $q2 = $tm->getData('082', 1, '2');
        if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
            $ddc = $tm->getData('082', 1, 'a');
        }
        if ($ddc == '') {
            $q = $tm->getData('083', 1, 'q');
            $q2 = $tm->getData('083', 1, '2');
            if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
                $ddc = $tm->getData('083', 1, 'a');
            }
            $q = $tm->getData('083', 2, 'q');
            $q2 = $tm->getData('083', 2, '2');
            if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
                $ddc .= " " . $tm->getData('083', 2, 'a');
            }
        }
        return $ddc;
    }

    function ort() {
        $tm = $this->tm;
        $vo = $c = '';
        $x = $tm->getData('264', 1, 'a');
        while ($x !== null) {
            $vo .= $c . $x;
            $c = ', ';
            $x = $tm->getData('264', 1, 'a');
        }
        return $vo;
    }

    function verlag() {
        $tm = $this->tm;
        $vl = $c = '';
        $x = $tm->getData('264', 1, 'b');
        while ($x !== null) {
            $vl .= $c . $x;
            $c = ', ';
            $x = $tm->getData('264', 1, 'b');
        }
        return $vl;
    }

    function physical() {
        $tm = $this->tm;
        $dc = $c = '';
        $x = $tm->getData('300', 1, '');
        while ($x !== null) {
            $dc .= $c . $x;
            $c = ', ';
            $x = $tm->getData('300', 1, '');
        }
        return $dc;
    }

    function index() {

        $x = $this->tm->getData('856', 1, '3');
        if ($x === 'Inhaltstext' || $x === 'Inhaltsverzeichnis') {
            $href = $this->tm->getData('856', 1, 'u');
        }
        return [$x, $href];
    }

    function content() {
        $x = $this->tm->getData('856', 2, '3');
        if ($x === 'Inhaltstext' || $x === 'Inhaltsverzeichnis') {
            $href = $this->tm->getData('856', 2, 'u');
        }
        return [$x, $href];
    }
}
