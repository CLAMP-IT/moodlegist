<?php

$app = require_once dirname(__DIR__).'/bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineDbalSingleTableAdapter;

// Uncomment next line to activate the debug
$app['debug'] = true;

///////////////////
// CONFIGURATION //
///////////////////

// Register the form provider
$app->register(new Silex\Provider\FormServiceProvider());

// Register twig templates path
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates',
));

// Configure Twig provider
$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {

    // Custom filter to handle version parsing from the DB.
    $formatVersions = new Twig_SimpleFilter('format_versions', function ($versions, $search) {
        $versions = json_decode($versions, true);
        $supportedversions = array();
        if ($search != 'any') {
            foreach ($versions as $key => $version) {
                $supportedmoodles = array();
                foreach ($version['supportedmoodles'] as $supportedmoodle) {
                    $supportedmoodles[] = $supportedmoodle['release'];
                }
                if (in_array($search, $supportedmoodles)) {
                    $supportedversions[$key] = $version;
                }
            }
        } else {
            $supportedversions = $versions;
        }

        $versionnumbers = array();
        foreach ($supportedversions as $key => $row) {
            $versionnumbers[$key] = $row['version'];
        }

        array_multisort($versionnumbers, SORT_ASC, $supportedversions);
        return $supportedversions;
    });

    $twig->addFilter($formatVersions);

    return $twig;
}));

// Register translation provider because the default Symfony form template require it
$app->register(new Silex\Provider\TranslationServiceProvider());

// Register Pagination provider
$app->register(new FranMoreno\Silex\Provider\PagerfantaServiceProvider());

// Register Url generator provider, required by the pager.
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Search Form
$searchForm = $app['form.factory']->createNamedBuilder('', 'form', null, array('csrf_protection' => false))
    ->setAction('search')
    ->setMethod('GET')
    ->add('q', 'search')
    ->add('moodle_version', 'choice', array(
        'choices' => array(
            'any' => 'Any version of Moodle',
            '3.4' => 'Moodle 3.4',
            '3.3' => 'Moodle 3.3',
            '3.2' => 'Moodle 3.2',
            '3.1' => 'Moodle 3.1',
            '3.0' => 'Moodle 3.0',
            '2.9' => 'Moodle 2.9',
            '2.8' => 'Moodle 2.8',
            '2.7' => 'Moodle 2.7',
        ),
    ))
    ->add('search', 'submit')
    ->getForm();

////////////
// ROUTES //
////////////

// Home
$app->get('/', function (Request $request) use ($app, $searchForm) {
    return $app['twig']->render('index.twig', array(
       'title'      => "Moodle Packagist: Manage your plugins with Composer",
       'searchForm' => $searchForm->handleRequest($request)->createView(),
    ));
});

// Search
$app->get('/search', function (Request $request) use ($app, $searchForm) {
    /** @var \Doctrine\DBAL\Query\QueryBuilder $queryBuilder */
    $queryBuilder = $app['db']->createQueryBuilder();
    $query        = trim($request->get('q'));

    $data = array(
        'title'              => "Moodle Packagist: Search packages",
        'searchForm'         => $searchForm->handleRequest($request)->createView(),
        'currentPageResults' => '',
        'error'              => '',
    );

    $queryBuilder
        ->select('*')
        ->from('packages', 'p');

    if (!empty($query)) {
        $queryBuilder
            ->andWhere('name LIKE :name')
            ->addOrderBy('name LIKE :order', 'DESC')
            ->addOrderBy('name', 'ASC')
            ->setParameter(':name', "%{$query}%")
            ->setParameter(':order', "{$query}%");
    } else {
        $queryBuilder
            ->addOrderBy('last_committed', 'DESC');
    }

    $countField = 'p.name';
    $adapter    = new DoctrineDbalSingleTableAdapter($queryBuilder, $countField);
    $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage(30);
    $pagerfanta->setCurrentPage($request->query->get('page', 1));

    $data['pager']              = $pagerfanta;
    $data['currentPageResults'] = $pagerfanta->getCurrentPageResults();
    $data['moodle_version']     = $request->get('moodle_version');

    return $app['twig']->render('search.twig', $data);
});

// Opensearch path
$app->get('/opensearch.xml', function (Request $request) use ($app) {
    return new Response(
        $app['twig']->render(
            'opensearch.twig',
            array('host' => $request->getHttpHost())
        ),
        200,
        array('Content-Type' => 'application/opensearchdescription+xml')
    );
});

$app->run();
