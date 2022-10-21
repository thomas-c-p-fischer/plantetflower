<?php

namespace App\Controller\Admin;

use App\Entity\Annonce;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AnnonceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Annonce::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setDisabled(),
            AssociationField::new('user', 'Utilisateur'),
            TextField::new('title', 'Titre'),
            TextEditorField::new('description', 'Description'),
            AssociationField::new('category', 'Catégorie'),
            BooleanField::new('sold', 'Vendu'),
            TextField::new('expNumber', 'Numéro expédition'),
            NumberField::new('price_origin', 'Prix d\'origine'),
            NumberField::new('price_total', 'Prix total'),
            DateTimeField::new('createdAt', 'Crée le'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('sold')
            ->add('category')
            ->add('price_origin')
            ->add('price_total')
            ->add('exp_number');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }
}
