# MaplePHP - Form builder
Create advanced, consistent and secure forms and validations.

## 1. Initiate
```php
use MaplePHP\Form\Fields;
use MaplePHP\Form\Examples\TestFormFields; // You should create you own template file for fields

$fields = new Fields(new TestFormFields());
```
*It is recommended that you make a copy off AbstractFormFields class, make it to a regualar class, rename it and extend it to the real AbstractFormFields abstract class. Then you can start making and adding your own custom input fields.*

### Basic: You can either quick create one field form the fields template
$fields->[FIELD_TYPE]->[ARG1]->[ARG2]->get();
**FIELD_TYPE:** Method name from Form\Templates\Fields
**ARG:** Chainable arguments like input name, fields attributes, validations and so on.
```php
echo $fields->text()->name("email")->label("Email address")->attr([
        "type" => "email", 
        "placeholder" => "Input your email..."
    ])->get();
```
## Advance:
Use the form compiler for advance consistent form creation and validation. Works really great in frameworks and large applications.

### Create fields
```
[
	inputFieldName => [
		// Field config…
	],
	…
	…
]
```

### Field config

#### type (string)
Expects defined form type key 
**Example:** text, textarea, date, select, checkbox, radio and more.
*Required*

#### label (string)
Define a input label
**Example:** Email address

#### description (string)
Define a input label
**Example:** We need your email to… 

#### attr (array)
Add html attributens to field
**Example:** 
```
[
	class => inp-email, 
	type => email,
	placeholder => Fill in the email
]
```
#### items (array)
Add checkbox, radio or select list items.
**Example:** 
```
[
	1 => Yes, 
	0 => No
]
```
*Is required for field types like select, checkbox and radio.*

#### validate (array)
Add validation to form field
**Example:** 
```
[
	length => [1, 200],
	!email => NULL
]
```
*The exclamation point before the email key means that it will **only** validate email if it is filled in else skip or do the other validation.*

#### config (multidimensional array)
Pass on custom data for a custom field.
**Example:** 
```
[
	role => admin,
	user_id => 5212
]
```
## Examples:
#### 1. Create form with array
Build a whole form with array as bellow
```php
$fields->add([
    "firstname" => [
        "type" => "text", // Set form type (input text or textarea and so on.)
        "label" => "First name",
        "validate" => [
            "length" => [1, 80]
        ]
    ],
    "lastname" => [
        "type" => "text",
        "label" => "Last name",
        "validate" => [
            "length" => [1, 120]
        ]
    ],
    "email" => [
        "type" => "text",
        "label" => "Email",
        "description" => "We need you email so that we can contact you.",
        "attr" => [
            "type" => "email",
            "placeholder" => "john.doe@hotmail.com"
        ],
        "validate" => [
            "length" => [1, 120],
            "!email" => NULL
        ]
    ],
    "nested,item1" => [
        "type" => "radio",
        "label" => "Question 1",
        "validate" => [
            "length" => [1],
        ],
        "items" => [
            1 => "Yes",
            0 => "No"
        ],
        "value" => 1 // Default value
    ],
    "nested,item2" => [
        "type" => "radio",
        "label" => "Question 2",
        "validate" => [
            "length" => [1],
        ],
        "items" => [
            1 => "Yes",
            0 => "No"
        ],
        "value" => 1 // Default value
    ],
    "message" => [
        "type" => "textarea",
        "label" => "Message",
        "validate" => [
            "length" => [0, 2000]
        ]
    ],
    "gdpr" => [
        "type" => "checkbox",
        //"label" => "GDPR",
        "validate" => [
            "length" => [1, 1],
            "!equal" => [1]
        ],
        "items" => [
            1 => "I accept that my data will be saved according to GDPR"
        ]
    ]
    
]);
```
#### 2. Set values if you want
If you have values from for example the database (accepts multidimensional array and object)
```php
$fields->setValues([
    "firstname" => "John",
    "lastname" => "John",
    "nested" => [
        "item1" => 0,
        "item2" => 1,
    ]
]);

```
#### 3. Build the form
You will allways need to build the form before read or validations.
```php
$fields->build();
```
#### 4. Read form
Now you can read the form.
```php
echo '<form action="index.php" method="post">';
echo $fields->getForm();
echo "</form>";
```
#### 5. Validate form
Now you can read the form.
```php
use MaplePHP\Form\Validate;

$fields->build();
$validate = new Validate($fields, $_POST);
if($error = $validate->execute()) {
    // HAS ERROR --> 
	echo "<pre>";
    print_r($error);
    echo "</pre>";

} else {
	// SUCCESS -->
	// Return filtered request (will only return values for added input fields)
	$request = $validate->getRequest(); // Uprotected
}

```




