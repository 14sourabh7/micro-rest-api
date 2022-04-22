<?php
return [
    "db" => [
        "username" => "m001-student",
        "password" => "12345",
    ],
    "api" => [
        "privatekey" => "xP+ZnsWx0SNevsk6fj4+eSZ6RaOIIn5vZK/3avpMT9+DsIwgXMOTvahbYq9JCdEdHr+/t9fkKyvMzrkwQiykIw==",
        "publickey" => "g7CMIFzDk72oW2KvSQnRHR6/v7fX5CsrzM65MEIspCM="
    ],
    "routes" => [
        // product
        "product" => [
            "get" => [
                ["/search/{keyword}", "search"],
                ["/get", "getAll"],
                ["/get/{id}", "getSingle"],
                ["/get/{per_page}/{page}/{select}/{filters}", "get"],
            ],
            "post" => [
                ["/post", "addProduct"],
            ],
            "put" => [
                ["/put", "updateProduct"],
            ],
            "delete" => [
                ["/delete/{id}", "deleteProduct"]
            ],
        ],

        // order
        "order" => [
            "get" => [
                ['/get', 'getAll'],
                ['/get/{start}/{end}', 'getDataByDate'],
                ['/get/{start}/{end}/{filter}', 'getDataByDateFilter']
            ],
            "post" => [
                ['/post', 'addOrder']
            ],
            "put" => [
                ['/put', 'updateOrder']
            ],
        ],

        // user
        "user" => [
            ['/', 'index'],
            ['/accesstoken', 'userAccesToken']
        ],

        //acl
        "acl" => [
            "get" => [
                ['/', 'index']
            ]
        ]
    ]
];
