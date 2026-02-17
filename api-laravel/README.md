# Cloud Gallery

### System Design

User get S3 directly upload URL ->
Draft/Processing image is created(opportunity to apply rules/account quotas) by backend ->
User perform upload directly to S3 -> 
S3 send upload notification/message to SNS/SQS ->
Lambda function creates thumbnail from message and upload it to destination S3 bucket ->
S3 send upload thumbnail notification/message to SNS/SQS ->
Backend change image status and handle search index of the new available image on OpenSearch


### Start project locally

```shell
sail up -d
```


