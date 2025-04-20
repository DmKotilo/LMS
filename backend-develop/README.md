# template-backend

## Шаблонный проект бэк

## Требования к системе:
- docker 20+
- docker-compose 2+
- make

## Запуск

Для удобства управления используем make:


0 Установка/обновление docker(через apt), docker-compose:
```
make setup-docker 
```

1 Заполняем переменные:
```
GROUP=
PROJECT=
```

2 Запуск проекта:
```
make start
```

3 Подготовительные манипуляции:
```
make compose
make build-front
make keygen
make storlink
make migrate
```

4 Остановка:
```
make stop
```

В последствии может потребоваться только выполнять миграции или установка зависимостей:
```
make compose
make migrate
```
