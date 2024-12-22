# php-dataset-clustering

Example of data clustering using Rubix ML and the K Means algorithm. Written in PHP 8.4 with Symfony Console.

## Most important classes

* [SolveTaskCommand](./src/UI/CLI/SolveTaskCommand.php)
* [DatasetClusterer](./src/Application/ML/DatasetClusterer.php)
* [ArrayDatasetRepository](./src/Infrastructure/Repository/ArrayDatasetRepository.php)
* [ClusteringAnalysisDatasetFactory](./src/Application/Analytics/ClusteringAnalysisDatasetFactory.php)

## Commands

```
Available commands for the "app" namespace:
  app:solve-task
```

## Copyrights

Copyright © Rafał Mikołajun 2024.
