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
class isbdElements extends tags2mem {

    function __construct($db) {
        parent::__construct($db);
    }

    function getAllTags($titleid) {
        $this->setTags($titleid);
    }

    function title() {
        $tia = [];
        $tia[] = $this->getData('245', 1, 'a');
        $tia[] = $this->getData('245', 1, 'b');
        $ti = implode(' / ', array_filter($tia));
        return $ti;
    }

    function author() {
        $au = $this->getData('100', 1, 'a');
        $au .= " " . $this->getData('100', 1, 'd');
        $au .= " " . $this->getData('100', 1, 'e');
        return trim($au);
    }

    function isbn() {
        return $this->getData('020', 1, '9');
    }

    function price() {
        return $this->getData('020', 1, 'c');
    }

    function ddc() {

        $ddc = '';
        $q = $this->getData('082', 1, 'q');
        $q2 = $this->getData('082', 1, '2');
        if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
            $ddc = $this->getData('082', 1, 'a');
        }
        if ($ddc == '') {
            $q = $this->getData('083', 1, 'q');
            $q2 = $this->getData('083', 1, '2');
            if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
                $ddc = $this->getData('083', 1, 'a');
            }
            $q = $this->getData('083', 2, 'q');
            $q2 = $this->getData('083', 2, '2');
            if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
                $ddc .= " " . $this->getData('083', 2, 'a');
            }
        }
        return $ddc;
    }

    function ort() {

        $vo = $c = '';
        $x = $this->getData('264', 1, 'a');
        while ($x !== null) {
            $vo .= $c . $x;
            $c = ', ';
            $x = $this->getData('264', 1, 'a');
        }
        return $vo;
    }

    function verlag() {

        $vl = $c = '';
        $x = $this->getData('264', 1, 'b');
        while ($x !== null) {
            $vl .= $c . $x;
            $c = ', ';
            $x = $this->getData('264', 1, 'b');
            $y = $this->getData('264', 1, 'c');
            if ($y !== null) {
                $x .= ' ' . $y;
            }
        }
        return $vl;
    }

    function physical() {

        $dc = $c = '';
        $x = $this->getData('300', 1, '');
        while ($x !== null) {
            $dc .= $c . $x;
            $c = ', ';
            $x = $this->getData('300', 1, '');
        }
        return $dc;
    }

    function indexEtAl() {
        $out = [];
        $i = 1;
        do {
            $x = $this->getData('856', $i, '3');
            $href = $this->getData('856', $i++, 'u');
            if ($x) {
                $out[] = ['x' => $x, 'h' => $href];
            }
        } while ($x != null);
        return $out;
    }

    function index() {
        $href = '';
        $x = $this->getData('856', 1, '3');
        if ($x === 'Inhaltstext' || $x === 'Inhaltsverzeichnis') {
            $href = $this->getData('856', 1, 'u');
        }
        return [$x, $href];
    }

    function content() {
        $href = '';
        $x = $this->getData('856', 2, '3');
        if ($x === 'Inhaltstext' || $x === 'Inhaltsverzeichnis') {
            $href = $this->getData('856', 2, 'u');
        }
        return [$x, $href];
    }

    function serie() {
        $x = $this->getData('A00', 1, '');

        return $x;
    }
}
