<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
</head>
<body class="bg-gray-100">
    <div class="p-10">
        <button id="trigger-toast" class="px-4 py-2 bg-blue-500 text-white rounded" onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Test message', type: 'success' } }))">Trigger Toast</button>
    </div>
    
    <x-toast />

    @fluxScripts
</body>
</html>
