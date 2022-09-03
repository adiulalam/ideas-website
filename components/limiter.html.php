  <form method="post" action="">
    <div class="flex flex-col my-auto items-center bgimg bg-cover p-3">
      <select name='limitData' onchange='this.form.submit()' class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <option value="">Limit: <?php if (isset($_POST["limitData"])) {
                                  echo $_POST["limitData"];
                                } else {
                                  echo 10;
                                }
                                ?></option>
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
    </div>
  </form>