<?php
/**
 * Author: Simon Enström
 * Version: 1.0
 * TODO: Add comments, Write docs
 */
class SettingsPage {
    private $controls = array();
    function __construct($id, $pageName, $subPage = null, $description = '', $section = array()){
        $this->pageName = $pageName;
        $this->id = $id;
        $this->sections = $section;
        $this->subPage = $subPage;
        $this->description = $description;
        add_action('admin_menu', array($this,'admin_menu_hook'));
        add_action('admin_init', array($this,'admin_init_hook'));
        
    }

    function addControl($type, $id, $label, $sectionId){
        $control;
        if($type == 'input'){
            $control = new TextInput($id, $label, $this->sections[$sectionId]);
        }elseif($type == 'textarea'){
            $control = new TextArea($id, $label, $this->sections[$sectionId]);
        }elseif($type == 'wysiwyg'){
            $control = new WYSIWYG($id, $label, $this->sections[$sectionId]);
        }elseif($type == 'checkbox'){
            $control = new Checkbox($id, $label, $this->sections[$sectionId]);
        }
        $this->controls[] = $control;
    }

    function addSection($sectionId, $groupId, $label){
        $this->sections[$sectionId] = array(
            'sectionId' => $sectionId,
            'groupId' => $groupId,
            'label' => $label
        );
    }

    function renderSettingsPage(){
        ?>
        <div class='wrap'>
        <h1>
            <?php echo $this->pageName; ?>
        </h1>
        <p><?php echo $this->description; ?></p>
        <br/>
        <form method='post' action='options.php'>
            <?php
                foreach($this->sections as $section){
                    settings_fields($section['sectionId']);
                    do_settings_sections($section['groupId']);    
                } 
                if(!$this->sections == array()){
                    submit_button(); 
                }
            ?>          
        </form>
        </div>
    <?php
    }

    function admin_init_hook(){
        //display theme panel fields
        foreach ($this->sections as $section){
            add_settings_section($section['sectionId'], $section['label'], null, $section['groupId']);
        }
        

        foreach ($this->controls as $control){
            add_settings_field($control->id, $control->label, array($control, 'render'), $control->section['groupId'], $control->section['sectionId']);
            register_setting($control->section['sectionId'], $control->id);
        }


    }

    function admin_menu_hook(){
        //add theme menu item
        if($this->subPage === null){
            add_menu_page($this->pageName, $this->pageName, 'manage_options', $this->id, array($this,'renderSettingsPage'), null, 99);
        }else{
            add_submenu_page($this->id, $this->subPage, $this->subPage, 'manage_options', $this->subPage, array($this,'renderSettingsPage'));
        }
        
    }
}

class TextInput {
    public $id;
    public $label;
    function __construct($id, $label, $section){
        $this->id = $id;
        $this->label = $label;
        $this->section = $section;
    }
    function render(){
        ?>
            <input class='regular-text' type='text' name='<?php echo $this->id ?>' id='<?php echo $this->id ?>' value='<?php echo get_option($this->id); ?>' />
        <?php
    }
}

class Checkbox {
    public $id;
    public $label;
    function __construct($id, $label, $section){
        $this->id = $id;
        $this->label = $label;
        $this->section = $section;
    }
    function render(){
        ?>
            <input type='checkbox' name='<?php echo $this->id ?>' id='<?php echo $this->id ?>' value="1" <?php checked('1', get_option($this->id), true); ?> />
        <?php
    }
}

class TextArea {
    public $id;
    public $label;
    function __construct($id, $label, $section){
        $this->id = $id;
        $this->label = $label;
        $this->section = $section;
    }
    function render(){
        ?>
            <textarea type='textarea' name='<?php echo $this->id ?>' id='<?php echo $this->id ?>'><?php echo get_option($this->id); ?></textarea>
        <?php
    }
}

class WYSIWYG{
    public $id;
    public $label;
    function __construct($id, $label, $section){
        $this->id = $id;
        $this->label = $label;
        $this->section = $section;
    }

    function render(){
        echo '<div style="width:500px">';
        wp_editor(get_option($this->id), $this->id, array('media_buttons'=>false, 'teeny'=>true, 'editor_height'=>200));
        echo '</div>';
    }
}

?>