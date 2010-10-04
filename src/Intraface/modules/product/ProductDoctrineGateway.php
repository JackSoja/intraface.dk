<?php
/**
 * Doctrine Gateway to ProductDoctrine
 *
 * Bruges til at holde styr på varerne.
 *
 * @package Intraface_Product
 * @author Sune Jensen
 * @see ProductDoctrine
 */

class Intraface_modules_product_ProductDoctrineGateway
{
    /**
     * @var object
     */
    private $user;

    /**
     * @var object doctrine record table
     */
    private $table;

    /**
     * Constructor
     *
     * @param object  $user                Userobject
     *
     * @return void
     */
    function __construct($doctrine, $user)
    {
        $this->user = $user;
        $this->table = $doctrine->getTable('Intraface_modules_product_ProductDoctrine');
    }

    /**
     * Finds a product with an id
     *
     * @param integer $id product id
     * @return object
     */
    function findById($id)
    {
        $collection = $this->table
            ->createQuery()
            ->select('*, details.*')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details AS details')
            ->addWhere('active = 1')
            ->addWhere('id = ?', $id)
            ->addOrderBy('details.id')
            ->execute();

        if ($collection == NULL || $collection->count() != 1) {
            throw new Intraface_Gateway_Exception('Error finding product from id '.$id);
        } else {
            return $collection->getLast();
        }

    }

    /**
     * Finds all products
     *
     * Hvis den er fra webshop bør den faktisk opsamle oplysninger om søgningen
     * så man kan se, hvad folk er interesseret i.
     * Søgemaskinen skal være tolerant for stavefejl
     *
     * @param object $search
     *
     * @return object collection containing products
     */
    public function findBySearch($search = '')
    {
        $collection = $this->table
            ->createQuery()
            ->select('*, details.*, variation.id, variation.*, variation_detail.*')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details details')
            ->leftJoin('Intraface_modules_product_ProductDoctrine.variation variation')
            ->innerJoin('variation.detail variation_detail')
            ->addWhere('active = 1')
            ->addOrderBy('details.number')
            // ->getSql(); die($collection);
            ->execute(array(), Doctrine::HYDRATE_ARRAY);

        return $collection;
    }

    public function findByVariationAttributeId($id) 
    {
        $collection = $this->table
            ->createQuery()
            ->select('*, details.*, details_translation.*, variation.id, variation.*, variation_detail.*')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details details')
            ->innerJoin('details.Translation details_translation')
            ->leftJoin('Intraface_modules_product_ProductDoctrine.variation variation')
            ->innerJoin('variation.detail variation_detail')
            ->leftJoin('variation.attribute1 variation_attribute1')
            ->leftJoin('variation.attribute2 variation_attribute2')
            ->addWhere('active = 1')
            ->addWhere('(variation_attribute1.product_attribute_id = ? OR variation_attribute2.product_attribute_id = ?)', array($id, $id))
            ->addOrderBy('details_translation.name')
            // ->getSqlQuery(); die($collection);
            ->execute();
        return $collection;
    }
        
    public function getMaxNumber()
    {
        $collection = $this->table
            ->createQuery()
            ->select('id, details.id, details.active, details.number')
            ->innerJoin('Intraface_modules_product_ProductDoctrine.details AS details')
            ->addWhere('Intraface_modules_product_ProductDoctrine.active = 0 OR Intraface_modules_product_ProductDoctrine.active = 1')
            ->orderBy('details.number')
            ->execute();

        if ($collection == NULL || $collection->count() == 0) {
            return 0;
        } else {
            return $collection->getLast()->getDetails()->getNumber();
        }
    }
}
