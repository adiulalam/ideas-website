<div class="<?php echo isMobileDevice() ? 'flex flex-row flex-wrap' :  'sm:flex' ?> justify-center py-1">
    <div class="<?php echo isMobileDevice() ? 'w-full' : '' ?> float left mx-5 my-1 ">
        <form method="get" action="?">
            <input type="hidden" name="page" value="<?php echo isset(($_GET['page'])) ? ($_GET['page']) : 1; ?>">
            <select Name="orderBy" ID="orderBy" class="border text-center <?php echo isMobileDevice() ? 'text-2xl' : 'text-sm' ?> rounded-lg block w-full p-2.5 px-6 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500" onchange='this.form.submit()'>
                <option class="text-sm" disabled="disabled" selected="selected">Order By: </option>
                <option class="text-sm" value="IdeaText ASC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "IdeaText ASC") echo "selected" ?>>Name ASC</option>
                <option class="text-sm" value="IdeaText DESC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "IdeaText DESC") echo "selected" ?>>Name DESC</option>
                <option class="text-sm" value="IdeaDate ASC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "IdeaDate ASC") echo "selected" ?>>Date ASC</option>
                <option class="text-sm" value="IdeaDate DESC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "IdeaDate DESC") echo "selected" ?>>Date DESC</option>
                <option class="text-sm" value="Vote ASC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "Vote ASC") echo "selected" ?>>Vote ASC</option>
                <option class="text-sm" value="Vote DESC" <?php if (isset($_GET["orderBy"]) && $_GET["orderBy"] == "Vote DESC") echo "selected" ?>>Vote DESC</option>
            </select>
            <input type="hidden" name="limitRecords" value="<?php echo isset(($_GET['limitRecords'])) ? ($_GET['limitRecords']) : 10; ?>">
            <input type="hidden" name="Author" value="<?php echo isset($_GET["Author"]) ? $_GET["Author"] : '' ?>">
            <input type="hidden" name="Category" value="<?php echo isset($_GET["Category"]) ? $_GET["Category"] : '' ?>">
            <input type="hidden" name="text" value="<?php echo isset($_GET["text"]) ? $_GET["text"] : '' ?>">
            <input type="hidden" name="action" value="search">
        </form>
    </div>

    <div class="<?php echo isMobileDevice() ? 'w-full' : '' ?> flex justify-center my-1">
        <form method="get" action="?">
            <ul class="flex">
                <li class="mx-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l <?php echo isMobileDevice() ? 'text-2xl' : 'text-sm' ?>">
                    <button class="block h-fit w-fit" name="page" type="submit" value="<?= $previous; ?>">&laquo; Prev</button>
                </li>
                <?php for ($i = 1; $i <= $pages; $i++) : ?>
                    <?php if ($i <= 5) { ?>
                        <li class=" mx-1 <?php echo $i == $page ? 'bg-gray-500' : 'bg-gray-300' ?> hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded  <?php echo isMobileDevice() ? 'text-2xl' : 'text-sm' ?>">
                            <button class="block h-fit w-fit" name="page" type="submit" value="<?= $i; ?>"><?= $i; ?></button>
                        </li>
                    <?php } else break; ?>
                <?php endfor; ?>

                <li class="mx-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r  <?php echo isMobileDevice() ? 'text-2xl' : 'text-sm' ?>">
                    <button class="block h-fit w-fit" name="page" type="submit" value="<?= $next; ?>">Next &raquo;</button>
                </li>
            </ul>
            <input type="hidden" name="orderBy" value="<?php echo isset(($_GET['orderBy'])) ? ($_GET['orderBy']) : 'IdeaDate DESC'; ?>">
            <input type="hidden" name="limitRecords" value="<?php echo isset(($_GET['limitRecords'])) ? ($_GET['limitRecords']) : 10; ?>">
            <input type="hidden" name="Author" value="<?php echo isset($_GET["Author"]) ? $_GET["Author"] : '' ?>">
            <input type="hidden" name="Category" value="<?php echo isset($_GET["Category"]) ? $_GET["Category"] : '' ?>">
            <input type="hidden" name="text" value="<?php echo isset($_GET["text"]) ? $_GET["text"] : '' ?>">
            <input type="hidden" name="action" value="search">
        </form>
    </div>

    <div class="<?php echo isMobileDevice() ? 'w-full' : '' ?> float right mx-5 my-1">
        <form method="get" action="?">
            <input type="hidden" name="page" value="<?php echo isset(($_GET['page'])) ? ($_GET['page']) : 1; ?>">
            <input type="hidden" name="orderBy" value="<?php echo isset(($_GET['orderBy'])) ? ($_GET['orderBy']) : 'IdeaDate DESC'; ?>">
            <select name="limitRecords" id="limitRecords" class="border text-center <?php echo isMobileDevice() ? 'text-2xl' : 'text-sm' ?> rounded-lg block w-full p-2.5 px-6 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500" onchange='this.form.submit()'>
                <option class="text-sm" disabled="disabled" selected="selected">Limit: </option>
                <?php foreach ([10, 20, 50, 100] as $offset) : ?>
                    <option class="text-sm" <?php if (isset($_GET["limitRecords"]) && $_GET["limitRecords"] == $offset) echo "selected" ?> value="<?= $offset; ?>"><?= 'Limit: ' . $offset; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="Author" value="<?php echo isset($_GET["Author"]) ? $_GET["Author"] : '' ?>">
            <input type="hidden" name="Category" value="<?php echo isset($_GET["Category"]) ? $_GET["Category"] : '' ?>">
            <input type="hidden" name="text" value="<?php echo isset($_GET["text"]) ? $_GET["text"] : '' ?>">
            <input type="hidden" name="action" value="search">
        </form>
    </div>
</div>