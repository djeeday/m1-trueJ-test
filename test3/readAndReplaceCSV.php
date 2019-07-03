<?php
class CSVOperator
{
    /**
     * Replace rows in csv-file with pattern
     */
    public function readAndReplaceCsv($fileTemp,$patternArray)
    {
        $file = fopen($fileTemp,'r', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $newFile = fopen('newFile.csv','w');
        // lets get columns row
        $columns = fgetcsv($file);
        fputcsv($newFile,$columns);
        //let's transform our $patternArray replacing name of column with it's index
        $patterns = $this->preparePattern($patternArray,$columns);
        //let's go down file
        while (($line = fgetcsv($file)) !== FALSE) {
            foreach($patterns as $k=>$v){
                $line[$k] = preg_replace($v['pattern'], $v['replacement'],$line[$k]);
            }
            fputcsv($newFile,$line);
        }
        fclose($file);
        fclose($newFile);
        return array('data' =>('newFile.csv'));
    }

    /**
     * changes column names to their index in row in current file
     * param array patternArray
     * param array
     * return array
     */
    public function preparePattern($patternArray,$columnsArray)
    {
        $modifiedPatternArray = [];
        foreach($patternArray as $k=>$v){
            $numberOfColumn = array_search($k,$columnsArray);
            if(!$numberOfColumn){
                throw new Exception("Error -  unknown column $k", 1);
            }
            $modifiedPatternArray[$numberOfColumn] = $v;
        }
        return $modifiedPatternArray;
    }
}
