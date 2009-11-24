== Ядро фреймворка BORS(C) ==
Сайт: http://bors.balancer.ru
Репозиторий: hg.balancer.ru
Автор: Balancer <balancer@balancer.ru>
Лицензия: GPLv3
Документация: встроенная, в подкаталоге data/fs/_bors/doc. 
				Она же - http://bors.balancer.ru/_bors/doc/

=== Порядок загрузки компонентов. ===

1. BORS_SITE/
2. BORS_LOCAL/vhosts/<hostname>/
3. BORS_LOCAL/
4. BORS_CORE/vhosts/<hostname>/
5. BORS_CORE/

=== Именования групп ===

bors_core - ядро системы
bors_common - общие расширения
bors_ext - расширения

BORS_SITE - настройки конкретного сайта-проекта
BORS_HOST - настройки конкретного хоста с конкретным проектом
BORS_LOCAL - локальные настройки машины
