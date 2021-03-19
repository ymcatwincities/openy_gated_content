# Virtual Y Personal training

This module provides base implementation of Virtual Y Personal trainings.

## Module structure

### PersonalTraining entity

| Field name | Field machine name | Field type | Description |
| ---------- | ----------- | ----------- | ----------- |
| Customer   | customer_id | entity reference | The drupal user ID of client of the Personal training entity. |
| Instructor | instructor_id | entity reference | The drupal user ID of instructor of the Personal training entity. |
| Customer metadata | customer_metadata | text_long | The metadata for the customer from CRM systems (It could be user id, email, or something else). |
| Instructor metadata | instructor_metadata | text_long | The metadata for the instructor from CRM systems (It could be user id, email, or something else). |
| Training type | training_type | list_string | One to one call on the Virtual YMCA platform or link to remote platform (zoom, google meet, etc). Available options: `1-1` and `link` |
| Remote Link | remote_link | link | The link to remote platform (zoom, google meet, etc). Used when Training type is `link`. |
| Training Date | date | daterange | Training start and end time. Stores the date in an ISO format `YYYY-MM-DDTHH:MM:SS` in `value` and `end_value` fields.|
| Created | created | created | The time that the Training was created.|
| Changed | changed | changed | The time that the Training was last edited. |

### PersonalTrainingProvider plugin

This module implements `PersonalTrainingProvider` annotation. You can find example
of plugin implementation in `src/Plugin/PersonalTrainingProvider`. Plugin should
contain annotation with `id`, `label` and `config` and implements
`PersonalTrainingProviderInterface` with `checkPersonalTrainingAccess` and
`getUserPersonalTrainings` methods.

## 3rd Party System Data

To save data from third-party systems to PTF you should create a custom module with
`PersonalTrainingProvider` plugin instance and implement sync tool for data fetch.
