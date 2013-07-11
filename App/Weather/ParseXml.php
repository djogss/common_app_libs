<?php
  class App_Weather_ParseXml
  {
      var $data;
      var $encoding;
      
      function ParseXML($encoding = 'UTF-8')
      {
        $this->encoding = $encoding;
      }

      function GetChildren($vals, &$i)
      { 
         $children = array();
         if (isset($vals[$i]['value'])){
            $children['VALUE'] = $vals[$i]['value'];
         } 
         
         while (++$i < count($vals))
         { 
            switch ($vals[$i]['type'])
            {
               
              case 'cdata': 
               if (isset($children['VALUE'])){
                  $children['VALUE'] .= $vals[$i]['value'];
               } else {
                  $children['VALUE'] = $vals[$i]['value'];
               } 
              break;
            
              case 'complete':
               if (isset($vals[$i]['attributes'])) {
                  $children[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
                  $index = count($children[$vals[$i]['tag']])-1;
         
                  if (isset($vals[$i]['value'])){ 
                     $children[$vals[$i]['tag']][$index]['VALUE'] = $vals[$i]['value']; 
                  } else {
                     $children[$vals[$i]['tag']][$index]['VALUE'] = '';
                  }
               } else {
                  if (isset($vals[$i]['value'])){
                     $children[$vals[$i]['tag']][]['VALUE'] = $vals[$i]['value']; 
                  } else {
                     $children[$vals[$i]['tag']][]['VALUE'] = '';
                  } 
               }
              break;
            
              case 'open': 
               if (isset($vals[$i]['attributes'])) {
                  $children[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
                  $index = count($children[$vals[$i]['tag']])-1;
                  $children[$vals[$i]['tag']][$index] = array_merge($children[$vals[$i]['tag']][$index],$this->GetChildren($vals, $i));
               } else {
                  $children[$vals[$i]['tag']][] = $this->GetChildren($vals, $i);
               }
              break; 
         
              case 'close': 
               return $children; 
            } 
         }
      }
      
      function GetXMLTree($xmlloc)
      { 
         $this->data = '';
         if (file_exists($xmlloc)){
            $this->data = implode('', file($xmlloc)); 
         } else {
        /*
            $fp = fopen($xmlloc,'r');
            while($fp && (!feof($fp))){
               $this->data = $this->data . fread($fp, 1024);
            }
            fclose($fp);
            */
            $fp = fopen($xmlloc,'rb');
            $this->data = stream_get_contents($fp);
            fclose($fp);
         }
      
         $parser = xml_parser_create($this->encoding);
         xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
         xml_parse_into_struct($parser, $this->data, $vals, $index); 
         xml_parser_free($parser); 
      
         $tree = array(); 
         $i = 0; 
      
         if (isset($vals[$i]['attributes'])) {
            $tree[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes']; 
            $index = count($tree[$vals[$i]['tag']])-1;
            $tree[$vals[$i]['tag']][$index] =  array_merge($tree[$vals[$i]['tag']][$index], $this->GetChildren($vals, $i));
         } else {
            $tree[$vals[$i]['tag']][] = $this->GetChildren($vals, $i); 
         }
         return $tree; 
      }
  }
