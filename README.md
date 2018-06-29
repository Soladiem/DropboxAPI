# Пример работы с Dropbox API

Здесь представлен небольшой пример работы с сервисом Dropbox API. Это вывод дерева папок и файлов, создание новых папок, загрузка пользовательских файлов, удаление папок или файлов.

### Установка
1. Необходимо наличие установленного [Composer](https://getcomposer.org/). Выполняем команду:
`$ php composer require kunalvarma05/dropbox-php-sdk`
2. Затем авторизируемся или регистрируемся на сайте Dropbox. Переходим по ссылке https://www.dropbox.com/developers/apps
3. Создаем новое приложение, нажав на кнопку **Create App**.
4. В результате вы получите *App key*, *App secret* и *Access Token*. Не забудьте указать адрес в **Redirect URIs** (у меня адрес как http://localhost)

В данном примера также используется бесплатный пакет Dropbox PHP SDK 
https://github.com/kunalvarma05/dropbox-php-sdk
  
Если все сделано правильно, то должно работать.
 