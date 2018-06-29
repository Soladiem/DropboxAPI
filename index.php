<?php
/**
 * @Doc https://github.com/kunalvarma05/dropbox-php-sdk
 */
require("vendor/autoload.php");

use Kunnu\Dropbox\{
	Dropbox,
	DropboxApp,
	DropboxFile
};

session_start();
ob_start();

// Настройки для приложения Dropbox
$app = new DropboxApp(
	'tb5xrifyj8xi4ks',
	'98iz6k4tgsovxu5',
	'rst_zIkorMAAAAAAAAAADMzQHRGVGR0Z1VEcRb7fValB5oWtKfUgfpArSLmSCD6k'
);

$redirect_uri = "http://{$_SERVER ['HTTP_HOST']}";

// Конфигурация сервиса Dropbox
$dropbox = new Dropbox($app);

// DropboxAuthHelper
$authHelper = $dropbox->getAuthHelper();

// Запрос на подтверждение работы с Dropbox-диском
if (isset($_REQUEST['code']) && isset($_REQUEST['state'])) {

	$code = $_REQUEST['code'];
	$state = $_REQUEST['state'];

	// Получить доступ к AccessToken
	$accessToken = $authHelper->getAccessToken($code, $state, $redirect_uri);
	$_SESSION['accessToken'] = $accessToken->getToken();
	header('Location:' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
} elseif (!isset($_SESSION['accessToken'])) {

	// Получить доступ к авторизации в Dropbox
	header('Location:' . filter_var($authHelper->getAuthUrl($redirect_uri), FILTER_SANITIZE_URL));
}

// Строим дерево элементов
$tree = makeTree($dropbox, "/");

echo '<style type="text/css">
	ul > li {
		list-style: none;
	}
	ul > li > img {
		margin-right: 3px;
	}
	
	.icon-new {
		display: inline-block;
		background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDIxIDc5LjE1NTc3MiwgMjAxNC8wMS8xMy0xOTo0NDowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkI1RTc1MjFBNjI1RTExRThCMEIwQzZBMUQyNjBFNTFEIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkI1RTc1MjFCNjI1RTExRThCMEIwQzZBMUQyNjBFNTFEIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QjVFNzUyMTg2MjVFMTFFOEIwQjBDNkExRDI2MEU1MUQiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QjVFNzUyMTk2MjVFMTFFOEIwQjBDNkExRDI2MEU1MUQiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7jjx+wAAABuklEQVR42pRSPYsUQRB9/TGO6y4KinuioGBgZGBgeNy/kLvEwOC4RC640NB/4I8QTEw88A+YCIrhqYFmBwfrutyyHzPd9WH13Gh4ck1N09NV9eq9qnaqiousOP/4dvr6QKsRFI5W15+8vLb19LyMr7u30vH7fHJIx++an2++74313OVlMK5u342DZbiyqO8M2iz/oTSdJdAEi1OwIq3re4+/PHtYjXwvrUg0rt452Hd1czuWW3YgURG3yg/2n0OkMwUzhPtf5xH9pxcHZwl2S5amRmfyw1noWRwTiJAzuLEyqKtm3cZk8GrmwYI009VMzW3RlEGMMES4jDhyEIfYEuI6EZpfOvms7byAUUFV27k/KGenlR9uuBv3SdBROj3CagrxhRV7GDGDK+eghZvXvJbFkf/9bTmfx2Vj7miiS5dMjDEp2CzcFwHbjai/BMmlraUCWWdbpdDTYDPGP1bd7q1UkMSIOWtBTR0wi1BS+tsf5hJtFXKWMhdPrFFCxKh2QxfIG7FgGgzeUIxh5o6kKfUIQOVtVHH8aOvDq8Oap2yOItEmK2bCYn5b6EYeA9qMm5s77qLP+48AAwD2V2eDUfQIogAAAABJRU5ErkJggg==");
		width: 16px;
		height: 16px;
	}
	.icon-upload{
		display: inline-block;
		background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDIxIDc5LjE1NTc3MiwgMjAxNC8wMS8xMy0xOTo0NDowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjBCNjg1MEQ0NjI1RjExRThBOTQ0RDIxMjczOUI5OTg0IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjBCNjg1MEQ1NjI1RjExRThBOTQ0RDIxMjczOUI5OTg0Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6MEI2ODUwRDI2MjVGMTFFOEE5NDREMjEyNzM5Qjk5ODQiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MEI2ODUwRDM2MjVGMTFFOEE5NDREMjEyNzM5Qjk5ODQiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6pslxlAAABb0lEQVR42oxSPUsDQRCd3du7yGkhpEghKQTthFTaKdraWdmqP8LKv2BnYWejjeQfiIWkEhWDoI2KihBJkUjI5+3uzDprQC/higwHNzP73ny8XeGcg5StniwWilMqCtm32tQ/B5Xd5zRAwaj1tN5e3nxsnrLf6sq313gMME7wRiKUOf4HLuMwg2BJIAF/3URORABCg5KrW8zqwFum4ziKtBF9I8iBNtjq9BcO8yOEEHJ7W/NOSJ6EQWRUtXEOpAhkPCPW1oOlwgrneUJCKF/cKj2gh/qNgBARyC8giSJkJq/hJGterd3BkMC6tTsK0VkrpABD4Ehax6eAKHh+a73P5S1juQRItKCsdi/vgU9482uWChsfVGHcV81yOB2lBExIdXvJ/dW/4N+9ZmkHhuOxXV82xlV6OnhLx8X9udk4nzTl71yCM52jfhqQcTUG+X3xaoGjyW4avSYB+RaTEf5EpAk7HJ+V27rlCVlv40eAAQDtXcpSmStOgAAAAABJRU5ErkJggg==");
		width: 16px;
		height: 16px;
	}
	.icon-delete {
		display: inline-block;
		background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDIxIDc5LjE1NTc3MiwgMjAxNC8wMS8xMy0xOTo0NDowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkU5NTRGNDZCNjI1RTExRTg4RUMwQTVGQzQyRTBGQTUxIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkU5NTRGNDZDNjI1RTExRTg4RUMwQTVGQzQyRTBGQTUxIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6RTk1NEY0Njk2MjVFMTFFODhFQzBBNUZDNDJFMEZBNTEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6RTk1NEY0NkE2MjVFMTFFODhFQzBBNUZDNDJFMEZBNTEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5pfvFyAAAB7UlEQVR42mL8//8/AwNDyZqc84/PPHh7/+vPLwyoQIBLUFVM3VDWpMmvE8hlBGqInhG2+8aW/7////vN8P/ff4b/CNWMTAyMzIyMLEDEYKZosyV/F6PbLB12lu8/f/z89evfn99///75D7ETApiYGJmYGVlZmVnZmFjZmL/+ZmL5z/jN0UyegTiw8sBZlo8f/l5784VIDe/f/2d59uDHs3dXidTw+yMnyNPLFq9WUlMkqPrerftRsaEsQNbnz1+FRYSQ5cp2qwDJLtc7yIIXz14BkiANr189FxYShkvU7zc2k04GMjrPWjTYn+ZiE4CIf3j/Bqrh65cfggL8cA1fmF64mfgDGVNOLpcWQwTgr19/oRqA7nnx4qWwqMj3Xx852fgnuD0CkkDxNqdLn76/+/bnkzCX7NvXbyQkxEAxA8QystIP7z/88vnL/ofTs9Zqff785QsY/f3OkrlO6+TTpUA2UIGSsiJUg7S05NVrN37++DF5VwM/g9Kv7/8/fPwARECGLr8vUBAoBVQgryALdZKtg/XePftBlnz48+Df/fqtYcFaxd9+f5p4PBXieqDU4wePVFLioBqA4OOHz/de3czXXwThfvv4k4GBHc4FSn3+9BXChmrQ1FY9ve/i77//sEYZE8N/HR1tCBsgwABAIuuiYfgrmwAAAABJRU5ErkJggg==");
		width: 16px;
		height: 16px;
	}
</style>';

// Вывод дерева
printTree($tree);

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		// Создание новой папки
		case 'newdir':

			if (isset($_GET['pid'])) {
				include 'form-create-dir.php';

				if ($_POST) {
					if (!empty($_POST['newFolder'])) {

						$affixPath = urldecode(base64_decode(htmlspecialchars($_GET['pid'])));
						$dirName = htmlspecialchars($_POST['newFolder']);
						$folder = $dropbox->createFolder("$affixPath/$dirName");
						header('Location:' . filter_var($redirect_uri, FILTER_SANITIZE_URL));

					} else {
						die('Заполните название папки');
					}
				}
			}

			break;

		// Загрузка файлов
		case 'upload':

			if (isset($_GET['fid'])) {
				include 'form-upload-file.php';

				if (isset($_FILES['Files']) && is_uploaded_file($_FILES['Files']['tmp_name'][0]) && ($_FILES['Files']['error'][0] == 0)) {
					$files = normalizeFilesArray($_FILES);
					$affixPath = urldecode(base64_decode(htmlspecialchars($_GET['fid'])));

					foreach ($files as $file) {
						$file = $dropbox->upload(
							DropboxFile::createByStream("$affixPath/{$file['name']}", file_get_contents($file['tmp_name'])),
							"$affixPath/{$file['name']}",
							['autorename' => true]
						);
					}

					header('Location:' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
				}
			}

			break;

		case 'delete':

			if (isset($_GET['fid'])) {
				$deletedFolder = $dropbox->delete(urldecode(base64_decode(htmlspecialchars($_GET['fid']))));
				header('Location:' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
			}

			break;
	}
}

ob_end_flush();

/** Функции */

function makeTree(Dropbox &$dropbox, $path)
{
	$branch = [];

	// Список файлов и папок
	$listFolderContents = $dropbox->listFolder($path);

	// Получить элементы
	$items = $listFolderContents->getItems();

	// Все элементы как массив
	$listFiles = $items->toArray();
    
	foreach ($listFiles as $k => $item) {
		$branch[$k] = [
			'id'   => substr($item->id, 3),
			'name' => $item->name,
			'path' => $item->path_display,
			'type' => $item->getData()['.tag'],
		];

		// Дата и время в нормальном виде
		if (isset($item->getData()['server_modified'])) {
			$branch[$k]['created'] = $item->getData()['server_modified'];
		}

		// Размер файла
		if ($item->size) {
			$branch[$k]['size'] = $item->size;
		}

		if ($item->getData()['.tag'] == 'folder') {
			$branch[$k]['children'] = makeTree($dropbox, $item->path_lower);
		}
	}
	return $branch;
}

function printTree($tree)
{
	if (!is_null($tree) && count($tree) > 0) {
		echo '<ul>';
		foreach ($tree as $kn => $node) {
			echo '<li>';

			if ($node['type'] == 'folder') {
				$url = 'https://www.dropbox.com/home' . $node['path'];
			}
			if ($node['type'] == 'file') {
				$url = 'https://www.dropbox.com/preview' . $node['path'] . '?role=personal';
			}

			echo '<a href="' . $url . '">';

			if ($node['type'] == 'folder') {
				echo '<b>' . $node['name'] . '</b>';
			} else {
				echo $node['name'];
			}

			echo '</a> ';

			if ($node['type'] == 'file') {
				echo date('d.m.Y H:i:s', strtotime($node['created']));
			}

			if ($node['type'] == 'folder') {
				echo ' <a href="?action=newdir&pid=' . base64_encode(urlencode($node['path'])) . '" title="Новая папка"><i class="icon-new"></i></a>';
			}
			echo ' <a href="?action=upload&fid=' . base64_encode(urlencode($node['path'])) . '" title="Загрузить файл(ы)"><i class="icon-upload"></i></a>';
			echo ' <a href="?action=delete&fid=' . base64_encode(urlencode($node['path'])) . '" title="Удалить файл(ы)" onclick="return confirm(\'Вы действительно хотите удалить данный файл?\');"><i class="icon-delete"></i></a>';

			if (isset($node['children'])) printTree($node['children']);
			echo '</li>';
		}
		echo '</ul>';
	}
}

/**
 * Приводим к нормальному виду глобальный массив $_FILES
 *
 * @param array $files
 * @return array
 */
function normalizeFilesArray($files = [])
{
	$result = [];

	foreach ($files as $file) {
		if (!is_array($file['name'])) {
			$result[] = $file;
			continue;
		}

		foreach ($file['name'] as $idx => $name) {
			$result[$idx] = [
				'name'     => $name,
				'type'     => $file['type'][$idx],
				'tmp_name' => $file['tmp_name'][$idx],
				'error'    => $file['error'][$idx],
				'size'     => $file['size'][$idx]
			];
		}
	}
	return $result;
}

/**
 * Вспомогательная функция для отладки
 *
 * @param $data
 */
function varDumper($data)
{
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}
