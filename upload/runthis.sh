#!/bin/bash
export DISPLAY=:4
#/usr/bin/fiji "$1" -run "my test" > /dev/null 2>&1 &
#!/bin/sh

set -e

. /etc/default/fiji

# If the user hasn't got JAVA_HOME set to a particular value, then
# either use FIJI_JAVA_HOME (which may be set in /etc/default/fiji) or
# the default, which is; /usr/lib/jvm/java-6-openjdk/

if [ -z "$JAVA_HOME" ]
then
    if [ -n "$FIJI_JAVA_HOME" ]
    then
        export JAVA_HOME=$FIJI_JAVA_HOME
    else
        export JAVA_HOME=/usr/lib/jvm/java-6-openjdk/
    fi
fi

export LD_LIBRARY_PATH=:$(cat /usr/lib/fiji/jni/*.conf 2> /dev/null | tr '\n' ':')

/usr/lib/fiji/fiji -Dfiji.debian=true --java-home "$JAVA_HOME" --class-path /usr/share/java/postgresql.jar:/usr/share/java/bsh.jar:/usr/share/java/jcommon.jar:/usr/share/java/jfreechart.jar:/usr/share/java/jzlib.jar:/usr/share/java/vecmath.jar:/usr/share/java/junit4.jar:/usr/share/java/itext1.jar:/usr/share/java/jama.jar:/usr/share/java/j3dutils.jar:/usr/share/java/clojure.jar:/usr/share/java/xml-apis-ext.jar:/usr/share/java/jsch.jar:/usr/lib/jvm/java-6-openjdk/lib/tools.jar:/usr/share/java/jna.jar:/usr/share/java/batik-all.jar:/usr/share/java/js.jar:/usr/share/java/j3dcore.jar --fiji-dir /usr/lib/fiji/ -- "$1" -run "my test" > /dev/null 2>&1 &



#/usr/lib/fiji/fiji -Dfiji.debian=true --java-home /usr/lib/jvm/java-6-openjdk/ --class-path /usr/share/java/postgresql.jar:/usr/share/java/bsh.jar:/usr/share/java/jcommon.jar:/usr/share/java/jfreechart.jar:/usr/share/java/jzlib.jar:/usr/share/java/vecmath.jar:/usr/share/java/junit4.jar:/usr/share/java/itext1.jar:/usr/share/java/jama.jar:/usr/share/java/j3dutils.jar:/usr/share/java/clojure.jar:/usr/share/java/xml-apis-ext.jar:/usr/share/java/jsch.jar:/usr/lib/jvm/java-6-openjdk/lib/tools.jar:/usr/share/java/jna.jar:/usr/share/java/batik-all.jar:/usr/share/java/js.jar:/usr/share/java/j3dcore.jar --fiji-dir /usr/lib/fiji/ -- "$1" -run my test > /dev/null 2>&1 &
MY_PID=$!
echo $MY_PID

