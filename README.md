# PHP parcel tracking API

## How to use

To use this API you need a running instance of Apache and MySQL. You can use XAMPP for example.

To use with XAMPP:

1. Clone this repository into your htdocs folder
2. Make sure you don't have a database named `users_parcels` in your MySQL instance (if you do, delete it)
   or change the name of the database in the `config.php` file

## API endpoints

To make requests to the API, you can use a number of things, for example Postman and import the collections from the `Postman` folder.

### User endpoints

#### `GET localhost/users`

Returns all users in the database:

```json
[
  {
    "id": 1,
    "first_name": "Zsombor",
    "last_name": "Balogh",
    "email_address": "zsombor.balogh@moonproject.io",
    "phone_number": null
  },
  {
    "id": 3,
    "first_name": "Jenő",
    "last_name": "Polgár",
    "email_address": "jeno.polgar@moonproject.io",
    "phone_number": "+36203114566"
  },
  {
    "id": 4,
    "first_name": "Mátyás",
    "last_name": "Király",
    "email_address": "matyas.kiraly@moonproject.io",
    "phone_number": null
  }
]
```

#### `POST localhost/users`

Adds a new user to the database. The request body should look like this:

```json
{
  "first_name": "Kázmér",
  "last_name": "Kovács",
  "email_address": "kazmer.kovacs@moonproject.io",
  "password": "Porcica01",
  "phone_number": "+36302131886"
}
```

The response will look like this:

```json
{
  "id": 6,
  "first_name": "Kázmér",
  "last_name": "Kovács",
  "email_address": "kazmer.kovacs@moonproject.io",
  "phone_number": "+36302131886"
}
```

### Parcel endpoints

#### `GET localhost/parcels/{parcel_number}`

Returns the parcel with the given parcel number

```json
{
  "id": 1,
  "parcel_number": "850f6335d7",
  "size": "M",
  "user": {
    "id": 3,
    "first_name": "Jenő",
    "last_name": "Polgár",
    "email_address": "jeno.polgar@moonproject.io",
    "phone_number": "+36203114566"
  }
}
```

#### `POST localhost/parcels`

Adds a new parcel to the database. The request body should look like this:

```json
{
  "size": "L",
  "user_id": 1
}
```

The response will look like this:

```json
{
  "parcel_number": "679cfadf3a",
  "size": "L",
  "user": {
    "id": 1,
    "first_name": "Zsombor",
    "last_name": "Balogh",
    "email_address": "zsombor.balogh@moonproject.io",
    "phone_number": null
  }
}
```
