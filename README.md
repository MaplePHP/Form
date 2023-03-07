# WA - Form builder
Validate form inputs.

### 1. Initiate
```
$fields = new Form\Fields(new Form\Templates\Fields());
```
### Basic: You can either quick create one field form the fields template
$fields->[FIELD_TYPE]->[ARG1]->[ARG2]->get();
**FIELD_TYPE:** Method name from Form\Templates\Fields
**ARG:** Chainable arguments like input name, fields attributes, validations and so on.
```
echo $fields->text()->name("email")->attr(["type" => "email"])->get();
```
### Advance:
Build a whole form that follows template and has built in validation
```
$fields->add("userForm", [
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
		"attr" => [
			"type" => "email",
			"placeholder" => "john.doe@hotmail.com"
		],
		"validate" => [
			"length" => [1, 120]
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

// Set form values
$fields->setValues([
	"firstname" => "John",
	"lastname" => "John",
	"nested" => [
		"item1" => 0,
		"item2" => 1,
	]
]);

// Build form
$fields->build();

// Read out form
echo $fields->form("userForm");

```