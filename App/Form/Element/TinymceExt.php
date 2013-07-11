<?php
class App_Form_Element_TinymceExt extends Zend_Form_Element_Textarea
{
    public function init()
    {
        $this->setLabel('Body')
          ->setRequired(true)
          ->addValidator('NotEmpty', true);
        $this->_addJs();
    }
    
    private function _addJs()
    {   
        $url = $this->getView()->absoluteUrl(array(), 'default', true);
        $url = substr($url,0,strlen($url)-2);
        $id = $this->getId();
        $this->getView()->headScript()->appendFile($this->getView()->BaseUrl('/js/tiny_mce/jquery.tinymce.js'), 'text/javascript');
        $this->getView()->headScript()->appendFile($this->getView()->BaseUrl('/js/tiny_mce/tiny_mce.js'), 'text/javascript');
        $this->getView()->headScript()->appendFile($this->getView()->BaseUrl('/js/imglib/css/imglib_tiny_manager.js'), 'text/javascript');
        $this->getView()->headScript()->captureStart();
        echo ' 
        tinyMCE.init({
            mode : "exact",
            elements : "'.$id.'",
            theme : "advanced", 
            
        plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true
});
//           imgLibManager.init({url: "'.$url.'"+"/fileupload"}); 

        ';
        $this->getView()->headScript()->captureEnd();
    }
}
