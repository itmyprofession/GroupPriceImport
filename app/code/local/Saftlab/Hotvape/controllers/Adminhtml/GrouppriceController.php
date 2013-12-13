<?php

class Saftlab_Hotvape_Adminhtml_GrouppriceController extends Mage_Adminhtml_Controller_Action
{

    protected static $_delimiter = ',';
    protected static $_enclosure = '"';

    public function indexAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            // upload file and save to folder
            $uploader = new Varien_File_Uploader("myform[filename]");
            $uploader->setAllowedExtensions(array('csv'));
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $path = Mage::getBaseDir('var') . DS . 'import' . DS . 'saftlab' . DS;
            $reponse = $uploader->save($path, null);

            if ($reponse) {
                $csv = new Varien_File_Csv();
                $csv->setEnclosure(self::$_delimiter);
                $csv->setEnclosure(self::$_enclosure);
                $rows = $csv->getData($reponse['path'] . $reponse['file']);
                array_shift($rows);

                Mage::helper('hotvape/groupprice')->import($rows);

                $success = Mage::helper('hotvape/groupprice')->getSuccessMessage();
                $failure = Mage::helper('hotvape/groupprice')->getFailureMessage();

                // Add succes and failure message
                if ($success)
                    Mage::getSingleton('adminhtml/session')->addSuccess($success);
                if ($failure)
                    Mage::getSingleton('adminhtml/session')->addError($failure);
            } else {
                throw new Exception('Please double check the file and data');
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }

}
