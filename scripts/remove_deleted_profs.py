#! /usr/bin/python

# 
# remove_deleted_profiles.py <userid> - Script to detect friend IDs in 
# <user>-prev.dat that were added due to friends disabling their profile, not 
# unfriending (bug)
#

import json
import urllib2
import sys

user_id = sys.argv[1]
friends_file = open('../userdata/' + user_id + '.dat', 'r')
del_friends_file = open('../userdata/' + user_id + '-prev.dat', 'r')

friends = json.load(friends_file)
del_friends = json.load(del_friends_file)
new_del_friends = []

#check if "deleted friends" still exist in friend list or if their profile is deleted
for cur_id in del_friends:
  if (friends.count(cur_id) == 0):
    cur_friend = json.load(urllib2.urlopen("https://api.facebook.com/method/users.getInfo?uids=" + cur_id + "&fields=name&format=json"))
    if (cur_friend != []):
      new_del_friends.append(cur_id)
      print cur_id

new_friends_json = json.dumps(new_del_friends)

#rewrite old friends file
del_friends_file.close()
new_del_friends_file = open('../userdata/' + user_id + '-prev.dat', 'w')
new_del_friends_file.write(new_friends_json)
