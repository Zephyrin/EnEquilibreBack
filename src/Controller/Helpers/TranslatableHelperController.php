<?php

namespace App\Controller\Helpers;

use Gedmo\Translatable\Translatable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This trait help for translation field.
 */
trait TranslatableHelperController
{
    private array $langs = [];

    public function setLang(array &$data, string $field) {
        if(isset($data[$field]))
        {
            $supported = $this->getParameter('app.supported_locales');
            foreach($supported as $locale) {
                if(isset($data[$field][$locale]))
                    $this->langs[$field][$locale] = $data[$field][$locale];
            }
            unset($data[$field]);
            if(isset($this->langs[$field]) && isset($this->langs[$field]['en'])) {
                $data[$field] = $this->langs[$field]['en'];
            }
        }
    }

    public function translate(Translatable $object, string $field, EntityManagerInterface $em, bool $clearMissing = false) {
        if(isset($this->langs[$field])) {
            $repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

            foreach($this->langs[$field] as $lang => $trans) {
                $repository->translate($object, $field, $lang, $trans);
            }
            $supported = $this->getParameter('app.supported_locales');
            if($clearMissing && count($this->langs[$field]) < count($supported)) {
                $translations = $repository->findTranslations($object);
                foreach($translations as $key => $translate) {
                    if(!isset($this->langs[$field][$key])) {
                        $repository->translate($object, $field, $key, null);
                    }
                }
            }
        }
    }
}