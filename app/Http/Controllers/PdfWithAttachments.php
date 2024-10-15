<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

class PdfWithAttachments extends Fpdi
{
    protected $embeddedFiles = [];

    public function embedFile($fileName, $fileContent)
    {
        $fileObjectIndex = $this->createEmbeddedFileObject($fileContent);
        $fileSpecIndex = $this->createFileSpecification($fileName, $fileObjectIndex);

        // Store the file spec index for later use when outputting the Names dictionary
        $this->embeddedFiles[] = $fileSpecIndex;
    }

    protected function createEmbeddedFileObject($fileContent)
    {
        $this->_newobj();
        $n = $this->n; // Save the object number

        $this->_out('<<');
        $this->_out('/Type /EmbeddedFile');
        $this->_out('/Length ' . strlen($fileContent));
        $this->_out('>>');
        $this->_putstream($fileContent);
        $this->_out('endobj');

        return $n;
    }

    protected function createFileSpecification($fileName, $embeddedFileObjIndex)
    {
        $this->_newobj();
        $n = $this->n; // Save the object number

        $this->_out('<<');
        $this->_out('/Type /Filespec');
        $this->_out('/F (' . $this->_escape($fileName) . ')');
        $this->_out('/EF << /F ' . $embeddedFileObjIndex . ' 0 R >>');
        $this->_out('/Desc (' . $this->_escape($fileName) . ')');
        $this->_out('>>');
        $this->_out('endobj');

        return $n;
    }

    protected function _putnames()
    {
        if (!empty($this->embeddedFiles)) {
            $this->_newobj();
            $namesObjectNumber = $this->n; // Save the object number

            $this->_out('<<');
            $this->_out('/Names [');
            foreach ($this->embeddedFiles as $fileSpecIndex) {
                $this->_out('(' . $this->_escape('EmbeddedFile' . $fileSpecIndex) . ') ' . $fileSpecIndex . ' 0 R');
            }
            $this->_out(']');
            $this->_out('>>');
            $this->_out('endobj');

            // Now create the EmbeddedFiles dictionary
            $this->_newobj();
            $embeddedFilesObjectNumber = $this->n;

            $this->_out('<<');
            $this->_out('/EmbeddedFiles ' . $namesObjectNumber . ' 0 R');
            $this->_out('>>');
            $this->_out('endobj');

            // Save the reference to the Names dictionary
            $this->namesObjectNumber = $embeddedFilesObjectNumber;
        }
    }

    protected function _putresources()
    {
        parent::_putresources();
        $this->_putnames();
    }

    protected function _putcatalog()
    {
        $this->_out('<<');
        $this->_out('/Type /Catalog');
        $this->_out('/Pages 1 0 R');

        if (isset($this->namesObjectNumber)) {
            $this->_out('/Names ' . $this->namesObjectNumber . ' 0 R');
        }

        // Include any other required entries
        $this->_out('>>');
    }
}
