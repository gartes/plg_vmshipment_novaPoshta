<?php defined('_JEXEC') or die;
class plgVmshipmentNova_pochtaInstallerScript{
 /**
Метод для установки компонента.
@param   object  $parent  Класс, который вызывает этом метод.
@return  void
 */
	public function install($parent){
         echo '<p>' . 'Плагин Установлен' . '</p>';
    }
   
    /**
     * Метод для удаления компонента.
     * @param   object  $parent  Класс, который вызывает этом метод.
	   @return  void
    */
    public function uninstall($parent){
       /* echo '<p>' . JText::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';*/
    }

    /**
    * Метод для обновления компонента.
    * @param   object  $parent  Класс, который вызывает этом метод.
    * @return  void
	*/
    public function update($parent){
        echo '<p>' . JText::sprintf('PLG_NOVAPOCHTA_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
    }
    /**
     * Метод, который исполняется до install/update/uninstall.
	 @param   object  $type    Тип изменений: install, update или discover_install
    * @param   object  $parent  Класс, который вызывает этом метод. Класс, который вызывает этом метод.
    * @return  void
    */
    public function preflight($type, $parent){
        /*echo '<p>' . JText::_('COM_HELLOWORLD_PREFLIGHT_' . strtoupper($type) . '_TEXT') . '</p>';*/
    }

    /**
     * Метод, который исполняется после install/update/uninstall.
     * @param   object  $type    Тип изменений: install, update или discover_install
     * @param   object  $parent  Класс, который вызывает этом метод. Класс, который вызывает этом метод.
     * @return  void
    */

    public function postflight($type, $parent){
       /* echo '<p>' . JText::_('COM_HELLOWORLD_POSTFLIGHT_' . strtoupper($type) . '_TEXT') . '</p>';*/
    }

}