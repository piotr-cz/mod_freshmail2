FreshMail Subscription Module ![JED icon](./artwork/JED_icon.png "mod_freshmail2")
=============================

[English documentation](https://github.com/piotr-cz/mod_freshmail2/blob/master/README.en.md)

Moduł zapisu do newslettera w systemie [FreshMail](http://freshmail.pl/)


Funkcje
-------
- Zarządzanie  polami dodatkowymi (Wyświetlane, Wymagane)
- Powiadomienie o nowym subksrybencie na email
- Opcja odnośnika do regulaminu w formularzu
- Układy (layouty) do wyboru
 - Horyzontalny (Twitter Bootstrap 2.3)
 - Rzędowy (Twitter Bootstrap 2.3)
 - Beez (Joomla 2.5)
 - Atomic (Joomla 2.5)
- Ograniczenie liczby wyświetleń lub ukrycie modułu po registracji
- Ajax (wymagana Joomla 3.1+)
- Wybór listy subkrypcyjnej


Wymagania
---------

- Joomla 2.5+
- PHP 5.3
- Konto w systemie FreshMail


Instalacja
----------

1. Pobierz najnowszą wersję z [Etykiet](https://github.com/piotr-cz/mod_freshmail2/tags) i zainstaluj za pomocą Instalatora Rozszerzeń Joomla _(Rozszerzenia > Instalacje > Instaluj z pakietu)_.
2. Dodaj moduł _(Rozszerzenia > Moduły > Utwórz > Freshmail Subscription)_


Konfiguracja
------------

1. Wstaw **Klucz API** konta Freshmail _(Opcje > Klucz API)_
2. Wstaw **API Sekret** _(Opcje > API Sekret)_
3. Wybierz Listę odbiorców _(Opcje > Lista odbiorców)_


### Opcje podstawowe

![Basic options](./artwork/screenshots/screen-admin-opcje-podstawowe.png "Basic options")

### Opcje wzbogacone

![Advanced options](./artwork/screenshots/screen-admin-opcje-wzbogacone.png "Advanced options")

### Układy

**Układ wertykalny**

![Vertical layout](./artwork/screenshots/screen-site-wetykalny.png "Vertical layout")

**Układ Horyzontalny**

![Horizontal layout](./artwork/screenshots/screen-site-horyzontalny.png "Horizontal layout")


Układy wyboru listy
-------------------

Domyślny układ używa typu _checkox_. Aby zmienić na inny, zmień kod w układzie odpowiedzialny za wyświetlanie list:

**Radio**

```php
<?php // Lists:Control ?>
<?php foreach ($lists as $i => $list) : ?>
	<label class="radio" title="<?php echo $list->description ?>">
		<input name="<?php echo $control ?>[list][]" type="radio" <?php if ($list->selected) : ?> checked="checked"<?php endif ?> value="<?php echo $list->subscriberListHash ?>" />
		<?php echo $list->name ?>
	</label>
<?php endforeach ?>
```

**Select**

```php
<?php // Lists:Control ?>
<select name="<?php echo $control ?>[list]" style="width: 100%">
<?php foreach ($lists as $i => $list) : ?>
	<option value="<?php echo $list->subscriberListHash ?>" <?php if ($list->selected) : ?> selected="selected"<?php endif ?>><?php echo $list->name ?></option>
<?php endforeach ?>
</select>
```

**Checkbox**

```php
<?php // Lists:Control ?>
<?php foreach ($lists as $i => $list) : ?>
	<label class="radio" title="<?php echo $list->description ?>">
		<input name="<?php echo $control ?>[list]" type="radio" <?php if ($list->selected) : ?> checked="checked"<?php endif ?> value="<?php echo $list->subscriberListHash ?>" />
		<?php echo $list->name ?>
	</label>
<?php endforeach ?>
```


Autorzy
-------

- [piotr_cz](https://github.com/piotr-cz)


Błędy/ Nowe funkcje
-------------------

[Zgłoś błąd lub propozycję nowej funkcji tutaj](https://github.com/piotr-cz/mod_freshmail2/issues)
