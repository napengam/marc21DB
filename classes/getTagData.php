<?php



/*
 * ***********************************************
 * read data from tags 
 * **********************************************
 */

class getTagData extends tags2mem {
    
    function __construct($db) {
        parent::__construct($db);
        
    }

    function getAllTags($titleid) {
        $this->setTags($titleid);
    }

    function ddc082() {
        /*
         * ***********************************************
         * DDC
         * **********************************************
         */
        $tm = $this;
        $ddc = '';
        $q = $tm->getData('082', 1, 'q');
        $q2 = $tm->getData('082', 1, '2');
        if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
            $ddc = $tm->getData('082', 1, 'a');
            return $ddc;
        }
        if ($ddc == '') {
            $q = $tm->getData('083', 1, 'q');
            $q2 = $tm->getData('083', 1, '2');
            if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
                $ddc = $tm->getData('083', 1, 'a');
                return $ddc;
            }
            $q = $tm->getData('083', 2, 'q');
            $q2 = $tm->getData('083', 2, '2');
            if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
                $ddc .= " " . $tm->getData('083', 2, 'a');
                return $ddc;
            }
        }
        return '';
    }
}
