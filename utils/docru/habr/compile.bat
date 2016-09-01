copy NUL compiled.md
dir *.md /b>filelist.tmp
for /f %%f in (filelist.tmp) do copy compiled.md + %%f compiled.md
del filelist.tmp
D:\OpenServer\domains\temp.temp\markdown\vendor\bin\markdown.bat compiled.md>html.html
pause