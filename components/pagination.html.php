<div class="flex justify-center py-2">
    <div class="float left mr-5">
        <form method="get" action="?">
            <input type="hidden" name="page" value="<?php echo isset(($_GET['page'])) ? ($_GET['page']) : 1; ?>">
            <select Name="orderBy" ID="orderBy" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange='this.form.submit()'>
                <option disabled="disabled" selected="selected">Order By: </option>
                <option value="IdeaText ASC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "IdeaText ASC") echo "selected" ?>>Name ASC</option>
                <option value="IdeaText DESC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "IdeaText DESC") echo "selected" ?>>Name DESC</option>
                <option value="IdeaDate ASC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "IdeaDate ASC") echo "selected" ?>>Date ASC</option>
                <option value="IdeaDate DESC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "IdeaDate DESC") echo "selected" ?>>Date DESC</option>
                <option value="Vote ASC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "Vote ASC") echo "selected" ?>>Vote ASC</option>
                <option value="Vote DESC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "Vote DESC") echo "selected" ?>>Vote DESC</option>
            </select>
            <input type="hidden" name="limitRecords" value="<?php echo isset(($_GET['limitRecords'])) ? ($_GET['limitRecords']) : 10; ?>">
        </form>
    </div>

    <form method="get" action="?">
        <ul class="flex">
            <li class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l">
                <button class="block h-fit w-fit" name="page" type="submit" value="<?= $previous; ?>">&laquo; Previous</button>
            </li>
            <?php for ($i = 1; $i <= $pages; $i++) : ?>
                <?php if ($i <= 5) { ?>
                    <li class=" mx-1 <?php echo $i == $page ? 'dark:bg-gray-500' : 'bg-gray-300' ?> hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        <button class="block h-fit w-fit" name="page" type="submit" value="<?= $i; ?>"><?= $i; ?></button>

                    </li>
                <?php } else break; ?>
            <?php endfor; ?>

            <li class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r">
                <button class="block h-fit w-fit" name="page" type="submit" value="<?= $next; ?>">Next &raquo;</button>
            </li>
        </ul>
        <input type="hidden" name="orderBy" value="<?php echo isset(($_GET['orderBy'])) ? ($_GET['orderBy']) : 'IdeaDate DESC'; ?>">
        <input type="hidden" name="limitRecords" value="<?php echo isset(($_GET['limitRecords'])) ? ($_GET['limitRecords']) : 10; ?>">
    </form>

    <div class="float right ml-5">
        <form method="get" action="?">
            <input type="hidden" name="page" value="<?php echo isset(($_GET['page'])) ? ($_GET['page']) : 1; ?>">
            <input type="hidden" name="orderBy" value="<?php echo isset(($_GET['orderBy'])) ? ($_GET['orderBy']) : 'IdeaDate DESC'; ?>">
            <select name="limitRecords" id="limitRecords" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange='this.form.submit()'>
                <option disabled="disabled" selected="selected">Limit: </option>
                <?php foreach ([10, 20, 50, 100] as $limit) : ?>
                    <option <?php if (isset($_GET["limitRecords"]) && $_GET["limitRecords"] == $limit) echo "selected" ?> value="<?= $limit; ?>"><?= $limit; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>