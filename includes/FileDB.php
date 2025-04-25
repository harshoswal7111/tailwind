<?php
class FileDB {
    private $dataPath;

    public function __construct() {
        $this->dataPath = __DIR__ . '/../data/';
        $this->initStorage();
    }

    private function initStorage() {
        $dirs = ['members', 'codes', 'sessions'];
        foreach ($dirs as $dir) {
            if (!file_exists($this->dataPath . $dir)) {
                mkdir($this->dataPath . $dir, 0700, true);
            }
        }
    }

    public function saveMember($member) {
        $id = uniqid('mem_');
        $member['id'] = $id;
        $member['created_at'] = time();
        $member['status'] = 'pending';
        
        return $this->saveToFile('members', $id, $member);
    }

    public function getMember($id) {
        return $this->getFromFile('members', $id);
    }

    public function getAllMembers($status = null) {
        $members = [];
        $files = glob($this->dataPath . 'members/*.json');
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (!$status || $data['status'] === $status) {
                $members[] = $data;
            }
        }
        
        return $members;
    }

    public function updateMemberStatus($id, $status) {
        $member = $this->getMember($id);
        if ($member) {
            $member['status'] = $status;
            return $this->saveToFile('members', $id, $member);
        }
        return false;
    }

    public function createCode() {
        $code = bin2hex(random_bytes(16));
        $data = [
            'code' => $code,
            'status' => 'unused',
            'created_at' => time()
        ];
        
        $this->saveToFile('codes', $code, $data);
        return $code;
    }

    public function validateCode($code) {
        $data = $this->getFromFile('codes', $code);
        if ($data && $data['status'] === 'unused') {
            return true;
        }
        return false;
    }

    public function markCodeUsed($code, $memberId) {
        $data = $this->getFromFile('codes', $code);
        if ($data) {
            $data['status'] = 'used';
            $data['used_at'] = time();
            $data['member_id'] = $memberId;
            return $this->saveToFile('codes', $code, $data);
        }
        return false;
    }

    private function saveToFile($type, $id, $data) {
        $file = $this->dataPath . $type . '/' . $id . '.json';
        $fp = fopen($file, 'c');
        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }
        return false;
    }

    private function getFromFile($type, $id) {
        $file = $this->dataPath . $type . '/' . $id . '.json';
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }
}