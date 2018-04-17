version 1.1.1
Bootstrap for Slim Framework

1.1.0 add CrudController and Crud route. Fix route attribute. Optimize.
1.1.1 fix Helper engine (__invoke with parameters)

CRUD route sample route

   ['routes'=>array(
      'client' => array(
        'method'     => 'CRUD',
        'route'      => '/section/client',
        'controller' => 'Section\Controller\ClientController',
        'role'       => 'user'
      ),
    ]


