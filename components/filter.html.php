<form action="" method="get">
    <div class="flex p-5">
        <div class="group inline-block mr-1">
            <select Name="Author" ID="Author" class="border <?php echo isMobileDevice() ? 'text-2xl' : 'text-sm' ?> rounded-lg block w-full p-2.5 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500">
                <option class="text-sm" selected="selected" value="">Authors:</option>
                <?php foreach ($authors as $Author) : ?>
                    <option class=" text-sm" <?php if (isset($_GET["Author"]) && $_GET["Author"] == $Author['ID']) echo "selected" ?> value="<?php html($Author['ID']); ?>"><?php html($Author['Name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="group inline-block mr-1">
            <select Name="Category" ID="Category" class="border <?php echo isMobileDevice() ? 'text-2xl' : 'text-sm' ?> rounded-lg block w-full p-2.5 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500">
                <option class="text-sm" selected="selected" value="">Categories:</option>
                <?php foreach ($categories as $Category) : ?>
                    <option class="text-sm" <?php if (isset($_GET["Category"]) && $_GET["Category"] == $Category['ID']) echo "selected" ?> value="<?php html($Category['ID']); ?>"><?php html($Category['Name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="relative w-full">
            <input type="search" name="text" id="text" value="<?php echo isset($_GET["text"]) ? $_GET["text"] : '' ?>" class="block p-2.5 w-full z-20 <?php echo isMobileDevice() ? 'text-2xl' : 'text-sm' ?> rounded-lg border-l-2 border focus:ring-blue-500 bg-gray-700 border-l-gray-700 border-gray-600 placeholder-gray-400 text-white focus:border-blue-500" placeholder="Search ideas">
            <input type="hidden" name="action" value="search">
            <button type="submit" class="absolute top-0 right-0 p-2.5 text-sm font-medium text-white rounded-r-lg border border-blue-700 focus:ring-4 focus:outline-none bg-blue-600 hover:bg-blue-700 focus:ring-blue-800">
                <svg aria-hidden="true" class="<?php echo isMobileDevice() ? 'w-8 h-8' : 'w-5 h-5' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span class="sr-only">Search</span>
            </button>
        </div>
    </div>
</form>