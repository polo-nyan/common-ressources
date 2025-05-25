url = "http://example.com/files/whatever.lua"
headers = {}
 
function getRemoteFile(url, file)
    fil = fs.open(file, fs.exists(file) and "a" or "w")
    fil.write(http.get( url, headers ).readAll())    
    fil.flush() -- Ensure data is written to disk, might not be necessary dont remember
    fil.close()
end
 
getRemoteFile(url, "startup.lua")