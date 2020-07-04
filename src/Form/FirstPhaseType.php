<?php

namespace App\Form;

use App\Entity\FirstPhase;
use App\Repository\CompetiteurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FirstPhaseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idJoueur1', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getAlmostBurntIdEquipe($builder->getData()->getIdEquipe());
                },
                'required'   => false,
                'empty_data' => null,
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c'); //TODO Bug "Brulé"

                    switch ($builder->getData()->getIdEquipe()) {
                        case 2:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2");
                            break;
                        case 3:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.2') < 2");
                            break;
                        case 4:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.2') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.3') < 2");
                            break;
                    }

                    return $query->orderBy('c.nom', 'ASC');
                }
            ))
            ->add('idJoueur2', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getAlmostBurntIdEquipe($builder->getData()->getIdEquipe());
                },
                'required'   => false,
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c'); //TODO Bug "Brulé"

                    switch ($builder->getData()->getIdEquipe()) {
                        case 2:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2");
                            break;
                        case 3:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.2') < 2");
                            break;
                        case 4:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.2') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.3') < 2");
                            break;
                    }

                    return $query->orderBy('c.nom', 'ASC');
                }
            ))
            ->add('idJoueur3', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getAlmostBurntIdEquipe($builder->getData()->getIdEquipe());
                },
                'required'   => false,
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c'); //TODO Bug "Brulé"

                    switch ($builder->getData()->getIdEquipe()) {
                        case 2:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2");
                            break;
                        case 3:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.2') < 2");
                            break;
                        case 4:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.2') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.3') < 2");
                            break;
                    }

                    return $query->orderBy('c.nom', 'ASC');
                }
            ))
            ->add('idJoueur4', EntityType::class, array(
                'class' => 'App\Entity\Competiteur',
                'choice_label' => function ($competiteur) use($builder) {
                    return $competiteur->getAlmostBurntIdEquipe($builder->getData()->getIdEquipe());
                },
                'label' => false,
                'attr'=> ['class'=>'browser-default'],
                'required'   => false,
                'empty_data' => null,
                'query_builder' => function (CompetiteurRepository $cr) use($builder) {
                    $query = $cr->createQueryBuilder('c'); //TODO Bug "Brulé"

                    switch ($builder->getData()->getIdEquipe()) {
                        case 2:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2");
                            break;
                        case 3:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.2') < 2");
                            break;
                        case 4:
                            $query
                                ->where("JSON_VALUE(c.brulage, '$.1') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.2') < 2")
                                ->orWhere("JSON_VALUE(c.brulage, '$.3') < 2");
                            break;
                    }

                    return $query->orderBy('c.nom', 'ASC');
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FirstPhase::class,
            'translation_domain' => 'forms'
        ]);
    }
}
