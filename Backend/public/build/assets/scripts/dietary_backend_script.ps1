
Set-Location -Path "C:\Users\MIS-Mark\Projects\New folder\dietary-api"


    $currentDateTime = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Output "Starting Laravel Reverb & Queues process... [$currentDateTime]"
    Write-Output "Process Stops After 8 Hours"
    try {
	$Queues_Process = Start-Process 'php' -ArgumentList 'artisan Queue:work' -PassThru
        $Reverb_Process = Start-Process 'php' -ArgumentList 'artisan reverb:start' -PassThru 
        Start-Sleep -Seconds 24400

        if (!$process.HasExited) {
            Write-Output "Stopping process..."
	    Stop-Process -Id $Queues_Process.Id
	    Stop-Process -Id $Reverb_Process.Id
        } else {
            Write-Output "Process exited on its own."
        }

    } catch {
        Write-Output "Error starting or stopping process: $_"
    }

    # Wait a bit before Stopping
    Start-Sleep -Seconds 2
    Write-Output "Ending Process."
	

