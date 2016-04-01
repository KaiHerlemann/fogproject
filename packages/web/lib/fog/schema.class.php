<?php
class Schema extends FOGController {
    protected $databaseTable = 'schemaVersion';
    protected $databaseFields = array(
        'id' => 'vID',
        'version' => 'vValue',
    );
    public function export_db($backup_name = '') {
        $file = '/tmp/fog_backup_tmp.sql';
        if (!$backup_name) $backup_name = sprintf('fog_backup_%s.sql',$this->formatTime('','Ymd_His'));
        $dump = self::getClass('Mysqldump');
        $dump->start($file);
        if (!file_exists($file) || !is_readable($file)) throw new Exception(_('Could not read tmp file.'));
        clearstatcache();
        $filesize = filesize($file);
        $fh = fopen($file,'rb');
        header("X-Sendfile: $filename");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Length: $filesize");
        header("Content-Disposition: attachment; filename=$file");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        while (feof($fh) === false) {
            echo fread($fh,4096);
            flush();
        }
        fclose($fh);
        unlink($file);
    }
    public function import_db($file) {
        $mysqli = self::$DB->link();
        if (false === ($fh = fopen($file,'rb'))) throw new Exception(_('Error Opening DB File'));
        while (($line = fgets($fh)) !== false) {
            if (substr($line,0,2) == '--' || $line == '') continue;
            $tmpline .= $line;
            if (substr(trim($line),-1,1) == ';') {
                if (false === $mysqli->query($tmpline)) $error .= _('Error performing query').'\'<strong>'.$line.'\': '.$mysqli->error.'</strong><br/><br/>';
                $tmpline = '';
            }
        }
        fclose($fh);
        if ($error) return $error;
        return true;
    }
}
