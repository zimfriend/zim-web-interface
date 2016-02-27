@echo on

SET basepath=%~dp0..\\
@rem echo %basepath:~0,-1%
echo %0

cd %basepath:~0,-1%

if "%1"=="unload" goto :unload
@rem if "%1"=="_remote_slice" goto :goRemoteSlice
if "%1"=="remote_slice" goto :remoteSlice
if "%1"=="remote_slice_stop" goto :remoteSliceStop
if "%1"=="clean_sliced" goto :cleanSliced
if "%1"=="clean_slicerfiles" goto :cleanSlicerFiles
if "%1"=="stats" goto :cleanStats

exit 0

@rem :goRemoteSlice
@rem ping -n 11 127.0.0.1 > nul
@rem echo {"state":"request"} > ./tmp/remote_slice.json
@rem goto :EOF

:cleanStats
del %2
goto :EOF

:cleanSliced
del .\tmp\_sliced_model.gcode*
del .\tmp\_sliced_info.json
goto :EOF

:cleanSlicerFiles
del .\tmp\_slicer_preview.amf
del .\tmp\_slicer_preview.stl
goto :cleanSliced

:remoteSlice
echo {"state":"working","extended":"0=edit ./tmp/remote_slice.json for test"} > ./tmp/remote_slice.json
@rem start /B %0 _remote_slice
goto :EOF

:remoteSliceStop
del .\tmp\remote_slice.json
goto :EOF

:unload
echo "in unloading"

if "%2"=="l" set extruder="T1"
if "%2"=="l" set unload_cmd="M1607"
if "%2"=="r" set extruder="T0"
if "%2"=="r" set unload_cmd="M1606"


setlocal
call :GetUnixTime UNIX_TIME
echo %UNIX_TIME% > ./tmp/printer_unload_heat
perl ./bin/arcontrol/Arcontrol_cli.pl %extruder%
perl ./bin/arcontrol/Arcontrol_cli.pl M104 S200
@rem timeout 60
ping -n 61 127.0.0.1 > nul
del .\tmp\printer_unload_heat
perl ./bin/arcontrol/Arcontrol_cli.pl %unload_cmd%
perl ./bin/arcontrol/Arcontrol_cli.pl M104 S20
goto :EOF

:GetUnixTime
setlocal enableextensions
for /f %%x in ('wmic path win32_utctime get /format:list ^| findstr "="') do (
    set %%x)
set /a z=(14-100%Month%%%100)/12, y=10000%Year%%%10000-z
set /a ut=y*365+y/4-y/100+y/400+(153*(100%Month%%%100+12*z-3)+2)/5+Day-719469
set /a ut=ut*86400+100%Hour%%%100*3600+100%Minute%%%100*60+100%Second%%%100
endlocal & set "%1=%ut%" & goto :EOF
