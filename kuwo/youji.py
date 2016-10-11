#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import requests
from bs4 import BeautifulSoup
import sys
import os

link=sys.argv[1]
r=requests.get(link)
html=r.text
soup = BeautifulSoup(html,"html5lib")
content=soup.find(id="content")
name=content.find("div",class_="zjtitlediv").get_text()
os.mkdir(name)

img=content.find_all('img')
urls=set([])
for im in img:
	urls.add(im['_src'])
    # print(im['_src'])
    
def downPic(urls):
	for url in urls:
		r=requests.get(url)
		picname=url.split("/")[-1]
		with open(name+"/"+picname,"wb") as f:
			for chunk in r.iter_content():
				f.write(chunk)

downPic(urls)			