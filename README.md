version 1.1.0 
Bootstrap for Slim Framework

1.1.0 add CrudController and Crud route. Fix route attribute. Optimize.

CRUD route sample route

   ['routes'=>array(
      'client' => array(
        'method'     => 'CRUD',
        'route'      => '/section/client',
        'controller' => 'Section\Controller\ClientController',
        'role'       => 'user'
      ),
    ]


