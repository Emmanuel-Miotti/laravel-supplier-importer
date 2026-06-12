# Importador de Tarifas de Proveedores

Este proyecto es una API hecha en Laravel para subir listas de precios en Excel de distintos proveedores y guardarlas de forma automática en la base de datos, sin tener que andar tocando o rompiendo el código que ya funciona.

---

## Cómo hacerlo andar (Setup)

**Importante antes de empezar:** Asegurate de tener activadas las extensiones `gd` y `zip` en tu archivo `php.ini` de XAMPP. Esto es obligatorio porque los archivos `.xlsx` son realmente paquetes comprimidos, y la librería `PhpSpreadsheet` los necesita para poder abrirlos, descomprimirlos en memoria y leer las celdas. (Luego de activarlas, acordate de reiniciar el Apache en XAMPP).

1. Cloná el proyecto y armá tu archivo de configuración local:  
   `cp .env.example .env`
2. Abrí el `.env`, buscá la línea `SESSION_DRIVER=database` y cambiala a:  
   `SESSION_DRIVER=file`
3. Instalá las dependencias del proyecto:  
   `composer install` (acá se descarga la librería para leer los Excel)
4. Generá la clave de seguridad de Laravel:  
   `php artisan key:generate`
5. Creá las tablas de la base de datos (usa SQLite automático) y levantá el servidor local corriendo:  
   `php artisan migrate` y después `php artisan serve`

*(Nota: El soporte para las rutas de la API quedó configurado usando el comando oficial `php artisan install:api`).*

---

## Pruebas Manuales (Postman)

Para probar el funcionamiento de la API de forma manual, podés usar estos dos endpoints en Postman con el servidor corriendo:

### 1. Subir y procesar un Excel (POST)
* **URL:** `http://127.0.0.1:8000/api/products/import`
* **Método:** `POST`
* **Configuración en Postman:**
  1. Entrá a la pestaña **Body**.
  2. Seleccioná la opción **form-data**.
  3. En la columna `Key` escribí `file` y cambialo de tipo *Text* a *File* (aparece un menú desplegable al pasar el mouse).
  4. En la columna `Value`, hacé clic en *Select Files* y elegí uno de los archivos Excel de prueba que están en la carpeta `ejemplos` del proyecto.
  5. *(Opcional)* Podés agregar otra fila en el form-data con la Key `provider_hint` y poner de valor el nombre del proveedor si querés saltearte la detección automática.

### 2. Consultar los productos guardados (GET)
* **URL:** `http://127.0.0.1:8000/api/products`
* **Método:** `GET`
* **Filtros de búsqueda (Pestaña Params - Opcionales):**
  * `brand`: Escribí una marca (ejemplo: `Sony`) para filtrar los productos.
  * `reference`: Escribí un código de referencia para buscar un producto específico.
* *Nota:* Este endpoint ya devuelve los datos paginados para asegurar que el sistema responda rápido aunque haya miles de registros guardados.

---

## Cómo se guardan los datos (Base de Datos)

Elegí separar la información en 4 tablas limpias para que todo quede bien organizado:

* **`suppliers`**: Guarda los nombres de los proveedores internacionales.
* **`products`**: Contiene la información fija del producto (código de referencia, marca, código EAN, dimensiones, familia y subfamilia). Cada producto sabe a qué proveedor pertenece.
* **`product_prices`**: Almacena los precios. Como un mismo producto puede tener distintos costos según la cantidad que compres (precios por volumen), los guardamos en esta tabla relacionada.
* **`product_taxes`**: Queda lista para guardar los impuestos especiales.

**¿Por qué lo hice así?** Podría haber guardado los precios y los impuestos como un bloque de texto largo (un JSON) adentro de la misma tabla de productos para terminar más rápido. Pero como el sistema tiene que soportar miles de filas y ser fácil de consultar, usar tablas separadas permite poner índices tradicionales y hacer que las búsquedas por marca o código vuelen.

---

## Cosas que di por sentado (Supuestos)

Como el enunciado tenía algunos puntos abiertos, tomé estas decisiones lógicas para entregar un proyecto que funcione y no quedarme trabado:

* **Impuestos Especiales:** La tabla de impuestos (`product_taxes`) quedó creada y conectada en la base de datos. Sin embargo, asumí que los proveedores no te mandan en su Excel cuánto cobrás de impuesto según el país donde vendés. Por eso el importador no los lee del archivo; di por sentado que esa info se maneja desde otro módulo interno de la empresa.
* **Tramos de Precios:** Los proveedores cambian la cantidad de escalas de precio según el producto. Para los dos archivos de ejemplo que armé, configuré el código para que lea hasta dos tramos de cantidad (`price` y `price_2`). Como la base de datos ya aguanta tramos infinitos, si mañana entra un proveedor con más escalas, solo hay que cambiar una función chica para que las recorra todos con un bucle.

---

## Cómo se organizó el código (Arquitectura)

Para evitar llenar el servicio de `if/else` interminables porque cada proveedor llama a las columnas como quiere (uno usa "REF", otro "Referencia", otro "Part Number"), usé una estructura limpia basada en mappers:

* **Mappers (`ProviderAColumnMapper`, etc.)**: Cada proveedor tiene su propio archivo chico donde dice cuáles son sus alias de columnas y si sus precios vienen con o sin IVA.
* **`ColumnMapperRegistry`**: Es el encargado de la detección automática. Lee la primera fila del Excel subido, mira los nombres de las columnas y adivina solo qué proveedor es.

Si mañana entra un "Proveedor C", solo hay que crearle su archivo Mapper con sus columnas y listo. El motor que importa todo (`ExcelImportService`) sigue intacto sin enterarse de los cambios.

---

## Pruebas (Tests)

Para correr las pruebas automáticas en la terminal ejecuta: `php artisan test`.

Escribí los tests para el detector de columnas (`ColumnMapperTest`) porque me parece la parte más delicada del sistema; si el usuario sube un Excel con nombres de columnas cambiados o raros, ahí es donde el sistema es más propenso a fallar.

**¿Qué dejé afuera y por qué?** Dejé afuera las pruebas que simulan subir archivos Excel físicos al disco. Hacer eso vuelve a los tests pesados, lentos y propensos a dar falsos errores por temas de permisos de la compu. Al asegurar mediante pruebas unitarias que la traducción de columnas funciona al 100%, el resto es simplemente guardar datos comunes mediante Eloquent.

---

## Qué mejoraría con más tiempo

* **Procesamiento de fondo (Queues)**: Si suben un Excel masivo de 50.000 filas, la petición web va a tirar un error de tiempo de espera (timeout). Lo ideal sería recibir el archivo, procesarlo en segundo plano y avisarle al usuario cuando termine.
* **Lectura por bloques (Chunking)**: En vez de cargar el archivo entero en la memoria de golpe, configuraría la librería para leer el Excel de a retazos para cuidar el consumo de RAM del servidor.
* **Reporte de errores**: Haría un endpoint para descargar un archivo con el detalle exacto de qué filas fallaron durante la importación y por qué razones (ej: "Fila 45: Falta la marca").