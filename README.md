# Экспорт заказов и клиентов в csv

1. Для экспорта данных и клиентов и заказов используется один сервис 
   - ```export/src/Service/Export/ExportManager.php```
2. Кнопки для экспорта находятся на страницах списка заказов и списка клиентов соответственно
3. При нажатии на кнопку экспорта вызывается метод  
   ```public function exportcsv(....)```
   в соответвующих разделам контролллерах:
   - ```export/src/Controller/OrderController.php```
   - ```export/src/Controller/CustomerController.php```
4. Следующие сервисы осуществляют подготовку данных и вызов экспорта, кастомизированно для каждого раздела
   - ```export/src/Service/Customer/CustomerExport.php```
   - ```export/src/Service/Order/OrderExport.php```
5. Особенность сервиса в том что экспорт происходит по выбранным фильтрам со страниц списков заказов и клиентов
