Usage
=====

Save an ez_option value in eZ Publish 5

```php
<?php

use Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option;

// Get service
$repository = $this->getContainer()->get('ezpublish.api.repository');
$userService = $repository->getUserService();
$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();
$contentTypeService = $repository->getContentTypeService();

// Identifying to the repository with a login and a password
$user = $userService->loadUserByLogin($login);
$repository->setCurrentUser($user);

// The ContentCreateStruct
$contentType = $contentTypeService->loadContentTypeByIdentifier('survey');
$contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');

// Create ez option
$option = new Option\Value();
$option->setName('Question - saved in eZ 5');
$option->addOption('test-1'); // same as $option->addOption(new Option\OptionElement('test-1')); // id =   -1 -> 0
$option->addOption(new Option\OptionElement('test-2'));                                          // id =   -1 -> 1
$option->addOption(new Option\OptionElement('test-3', null, 1000));                              // id = 1000 -> 1000
$option->addOption(new Option\OptionElement('test-4', 10));                                      // id =   -1 -> 1001
$option->addOption(new Option\OptionElement('test-5', 20, 5));                                   // id =    5 -> 5

/*
It is equivalent to this code :
$options = array(
   'test-1',
   new Option\OptionElement('test-2'),
   new Option\OptionElement('test-3', null, 1000),
   new Option\OptionElement('test-4', 10),
   new Option\OptionElement('test-5', 20, 5),
);
$option = new Option\Value('Question - saved in eZ 5', $options);

or

$options = array(
   'test-1',
   'test-2',
);
$option = new Option\Value('Question - saved in eZ 5', $options);
$options = array(
   new Option\OptionElement('test-3', null, 1000),
   new Option\OptionElement('test-4', 10),
   new Option\OptionElement('test-5', 20, 5),
);
$option->addOptions($options);
*/

// Setting the fields values
$contentCreateStruct->setField('expiry', new \DateTime());
$contentCreateStruct->setField('question_answers', $option);

// Setting the Location
$locationCreateStruct = $locationService->newLocationCreateStruct(274);

// Creating and publishing
$draft = $contentService->createContent($contentCreateStruct, array( $locationCreateStruct ));
$content = $contentService->publishVersion($draft->versionInfo);
```
