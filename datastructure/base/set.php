<?php

class Set{
    
    public $dataStore;
    
    public function __construct()
    {
        $this->dataStore = [];
    }

    function add($data) {
        if (!in_array($data, $this->dataStore)) {
            array_push($this->dataStore, $data);
            return true;
        } else {
            return false;
        }
    }

    function remove($data) {
        $pos = array_search($data, $this->dataStore);
        if (!$pos) {
            unset($this->dataStore[$pos]);
            return true;
        } else {
            return false;
        }
    }

    function show() {
        return $this->dataStore;
    }

    function contains($data) {
        if (in_array($data, $this->dataStore)) {
            return true;
        } else {
            return false;
        }
    }

    function union($set) {
        $tempSet = new Set();
        for ($i = 0, $len = count($this->dataStore); $i < $len; ++$i) {
            $tempSet->add($this->dataStore[$i]);
        }
        for ($i = 0, $leng = count($set->dataStore); $i < $leng; ++$i) {
            if (!$tempSet->contains($set->dataStore[$i])) {
                array_push($tempSet->dataStore, $set->dataStore[$i]);
            }
        }
        return $tempSet;
    }

    function intersect($set) {
        $tempSet = new Set();
        for ($i = 0, $len = count($this->dataStore); $i < $len; ++$i) {
            if ($set->contains($this->dataStore[$i])) {
                $tempSet->add($this->dataStore[$i]);
            }
        }
        return $tempSet;
    }

    function subset($set) {
        if ($this->size() > $set->size()) {
            return false;
        } else {
            foreach ($this->dataStore as $member) {
                if (!$set->contains($member)) {
                    return false;
                }
            }
        }
        return true;
    }

    function size() {
        return count($this->dataStore);
    }

    function difference($set) {
        $tempSet = new Set();
        for ($i = 0, $len = count($this->dataStore); $i < $len; ++$i) {
            if (!$set->contains($this->dataStore[$i])) {
                $tempSet->add($this->dataStore[$i]);
            }
        }
        return $tempSet;
    }

}


$names = new Set();
$names->add("David");
$names->add("Jennifer");
$names->add("Cynthia");
$names->add("Mike");
$names->add("Raymond");
if ($names->add("Mike")) {
    print_r("Mike added");
} else {
    print_r("Can't add Mike, must already be in set");
}
print_r($names->show());
$removed = "Mike";
if ($names->remove($removed)) {
    print_r($removed . " removed.");
} else {
    print_r($removed . " not removed.");
}
$names->add("Clayton");
print_r($names->show());
$removed = "Alisa";
if ($names->remove("Mike")) {
    print_r($removed . " removed.");
} else {
    print_r($removed . " not removed.");
}