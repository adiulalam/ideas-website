<div class="flex justify-center my-2">
    <div class="float right mr-5">
        <form method="post" action="#">
            <select Name="orderBy" ID="orderBy" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange='this.form.submit()'>
                <option disabled="disabled" selected="selected">Order By: </option>
                <option value="IdeaText ASC">Name ASC</option>
                <option value="IdeaText DESC">Name DESC</option>
                <option value="IdeaDate ASC">Date ASC</option>
                <option value="IdeaDate DESC">Date DESC</option>
                <option value="Vote ASC">Vote ASC</option>
                <option value="Vote DESC">Vote DESC</option>
            </select>
        </form>
    </div>

    <ul class="flex">
        <li class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l">
            <a href="index.php?page=<?= $Previous; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo; Previous</span>
            </a>
        </li>
        <?php for ($i = 1; $i <= $pages; $i++) : ?>
            <?php if ($i <= 5) { ?>
                <li class=" mx-1 <?php echo $i == $page ? 'dark:bg-gray-500' : 'bg-gray-300' ?> hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"><a class="block h-fit w-fit" href="index.php?page=<?= $i; ?>"><?= $i; ?></a></li>
            <?php } else break; ?>
        <?php endfor; ?>
        <li class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r">
            <a href="index.php?page=<?= $Next; ?>" aria-label="Next">
                <span aria-hidden="true">Next &raquo;</span>
            </a>
        </li>
    </ul>

    <div class="float right ml-5">
        <form method="post" action="#">
            <select name="limit-records" id="limit-records" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange='this.form.submit()'>
                <option disabled="disabled" selected="selected">Limit: </option>
                <?php foreach ([10, 20, 50, 100] as $limit) : ?>
                    <option <?php if (isset($_POST["limit-records"]) && $_POST["limit-records"] == $limit) echo "selected" ?> value="<?= $limit; ?>"><?= $limit; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>