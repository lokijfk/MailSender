# <span style=“color:red;”> branch archiwalny, nie będzie rozwijany  </span>
# MailSender
Pierwotnie dodatek do aplikacji wspomagania uczelni dla uczelni wyższej,
msłuży do wysyłki masowej maili spersonalizowanych do wykładowców.
Dane wykładowców były pobierane z bazy postgresql parsowane przez skrypty aplikacji napisane w php.
Nestety samodzielnie nie działa.

## Całość wykożystuje:
* PHP 7.4+
* Smarty 4.3+
* PhpMailer 6.7+
* Monolog
## zawartość katalogów

###### Smarty\templates\bodies\ - zawiera templaty smarty:
* PwnWys.tpl - wywoływany jako pierwszy, tu powinny być wszystkie najważniejsze ustawienia.
* PwnView.tpl - jest podglądem wysyłanych danych, wywoływany jako drugi o ilejest ustawiony jakikolwiek rodzaj podglądu.
* PwnSend.tpl - jest podglądem procesu wysyłania maili i jestwysyłany jako ostatni.
* TerminyInneDni.tpl - jest plikiem wspomagającym.

###### Component - Zawiera pliki obiektów dodatkowych:
* ACL.php - kontrola uprawnień, obiekt w trakcie rozbudowy
* Log.php - dodatkowa obsługa logów poprzez monolog
* Tools.php - obiekt z funkcjami pomocniczymi wykożystywanymi w kilku miejscach, w trakcie rozbudowy
* Rejestr.php - obiekt Singleton, na razie przechowuje obiekty bazy, log i tools

###### swod - zawiera katalogi z plikami według koncepcji aplikacji do której był pisany dodatek
* swod\ajax - odpowiedzi z zapytani ajax\jquery
* swod\BD - połączenie z bazą danych
* swod\Bodies - uzupełnienie danych w template, pobranych między innymi z bazy
* swod\Titles - wywołanie template 
