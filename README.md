# API requests

## get suppliers
Request:

`GET http://localhost/boolean_it_project/suppliers`

Response:

```
[
    {"id":1,"name":"Luigi Tech rrrr"},
    {"id":2,"name":"Tech suppliers llc"}
]
```

## get supplier
Request:

`GET http://localhost/boolean_it_project/suppliers/2`

Response:

```
{"id":2,"name":"Tech suppliers llc"}
```

## delete supplier
Request:

`DELETE http://localhost/boolean_it_project/suppliers/2`

## get products of the supplier
Request:

`GET http://localhost/boolean_it_project/suppliers/2/products`

Part of response:

```
[
    {
        "id": 11,
        "partNumber": "BA62-00568A",
        "supplierId": 2,
        "partDesc": "Lenovo Yoga 20G8 11e Winbook Webcam Camera SC20F26987 Tested Warranty",
        "price": "10.00",
        "quantity": 14,
        "priority": 1,
        "daysValid": 15,
        "conditionId": 1,
        "categoryId": 6
    },
    {
        "id": 16,
        "partNumber": "04W6858",
        "supplierId": 2,
        "partDesc": "Lenovo Chromebook Thinkpad 11e-20GF Webcam Camera 00HN347",
        "price": "10.00",
        "quantity": 37,
        "priority": 6,
        "daysValid": 15,
        "conditionId": 1,
        "categoryId": 6
    },
    {
        "id": 169,
        "partNumber": "856680-001",
        "supplierId": 2,
        "partDesc": "HP Pavilion DM3T-3000 Heatsink & Fan 619440-001",
        "price": "10.00",
        "quantity": 9,
        "priority": 6,
        "daysValid": 15,
        "conditionId": 1,
        "categoryId": 92
    },
   ...
]
```

## get one product of the supplier
Request:

`GET http://localhost/boolean_it_project/suppliers/2/products/299`

Response:

```
{
    "id": 299,
    "partNumber": "K509X",
    "supplierId": 2,
    "partDesc": "Lenovo Thinkpad X200 DC Jack Cable 44C5396",
    "price": "10.00",
    "quantity": 44,
    "priority": 10,
    "daysValid": 15,
    "conditionId": 1,
    "categoryId": 92
}
```

## get all products
Request:

`GET http://localhost/boolean_it_project/products`

Part of response:
```
[
    {
        "id": 1,
        "partNumber": "H000082851",
        "supplierId": 1,
        "partDesc": "5250 AIO WEB CAMERA CABLEe bbb fd",
        "price": "10.00",
        "quantity": 13,
        "priority": 1,
        "daysValid": 8,
        "conditionId": 1,
        "categoryId": 37
    },
    {
        "id": 7,
        "partNumber": "04X3829",
        "supplierId": 1,
        "partDesc": "FRONT CAMERA 2M ",
        "price": "10.00",
        "quantity": 23,
        "priority": 8,
        "daysValid": 15,
        "conditionId": 1,
        "categoryId": 6
    },
    {
        "id": 10,
        "partNumber": "BA62-00569A",
        "supplierId": 1,
        "partDesc": "Camera 720P Front MIC WTB Chny",
        "price": "10.00",
        "quantity": 32,
        "priority": 1,
        "daysValid": 15,
        "conditionId": 1,
        "categoryId": 6
    },
    {
        "id": 11,
        "partNumber": "BA62-00568A",
        "supplierId": 2,
        "partDesc": "Lenovo Yoga 20G8 11e Winbook Webcam Camera SC20F26987 Tested Warranty",
        "price": "10.00",
        "quantity": 14,
        "priority": 1,
        "daysValid": 15,
        "conditionId": 1,
        "categoryId": 6
    },
    ...
```

## delete product
Request:

`DELETE http://localhost/boolean_it_project/products/8`
