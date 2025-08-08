import React, { useEffect, useState } from 'react';
import { StatusBox } from './components/status-box';
import { SettingsForm } from './components/settings-form';
import { AdminBar } from './components/admin-bar';

declare global {
  interface Window {
    llmsTxtAdmin: {
      ajaxUrl: string;
      nonce: string;
      rootPath: string;
      isWritable: boolean;
      lastGenerated: string | null;
      pluginUrl: string;
      settings: {
        post_types: string[];
        full_content: boolean;
        days_to_include: number;
        delete_on_uninstall: boolean;
      };
      postTypes: Array<{
        name: string;
        label: string;
      }>;
    };
  }
}

export default function App() {
  console.log('App component rendering');
  
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [data, setData] = useState<{
    rootPath: string;
    isWritable: boolean;
    lastGenerated: string | null;
    settings: {
      post_types: string[];
      full_content: boolean;
      days_to_include: number;
      delete_on_uninstall: boolean;
    };
    postTypes: Array<{
      name: string;
      label: string;
    }>;
  } | null>(null);

  useEffect(() => {
    console.log('App useEffect running');
    console.log('Window llmsTxtAdmin:', window.llmsTxtAdmin);
    
    // Get data from the global window object
    try {
      if (window.llmsTxtAdmin) {
        console.log('Found llmsTxtAdmin data:', window.llmsTxtAdmin);
        
        setData({
          rootPath: window.llmsTxtAdmin.rootPath,
          isWritable: window.llmsTxtAdmin.isWritable,
          lastGenerated: window.llmsTxtAdmin.lastGenerated,
          settings: window.llmsTxtAdmin.settings,
          postTypes: window.llmsTxtAdmin.postTypes,
        });
      } else {
        console.error('llmsTxtAdmin data not found in window object');
        setError('WordPress admin data not found.');
      }
    } catch (err) {
      console.error('Error loading data:', err);
      setError('Error loading data.');
    } finally {
      setIsLoading(false);
    }
  }, []);

  const handleGenerateNow = async () => {
    try {
      const formData = new FormData();
      formData.append('action', 'llmagnet_ai_seo_generate_now');
      formData.append('nonce', window.llmsTxtAdmin.nonce);

      const response = await fetch(window.llmsTxtAdmin.ajaxUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });

      const result = await response.json();

      return {
        success: result.success,
        message: result.data?.message || 'Unknown error',
        timestamp: result.data?.timestamp,
      };
    } catch (error) {
      console.error('Error generating LLMS.txt:', error);
      return {
        success: false,
        message: 'Error generating LLMS.txt. Please check server permissions.',
      };
    }
  };

  const handleSaveSettings = async (newSettings: any) => {
    try {
      const formData = new FormData();
      formData.append('action', 'llmagnet_ai_seo_save_settings');
      formData.append('nonce', window.llmsTxtAdmin.nonce);
      formData.append('settings', JSON.stringify(newSettings));

      const response = await fetch(window.llmsTxtAdmin.ajaxUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
      });

      const result = await response.json();

      if (result.success) {
        alert('Settings saved successfully.');
        
        // Update local state with new settings
        if (data) {
          setData({
            ...data,
            settings: newSettings,
          });
        }
      } else {
        alert('Error saving settings: ' + (result.data?.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('Error saving settings:', error);
      alert('Error saving settings. Please try again.');
    }
  };

  console.log('App render state:', { isLoading, error, data });

  if (isLoading) {
    return <div className="p-4">Loading...</div>;
  }

  if (error || !data) {
    return <div className="p-4 text-red-600">{error || 'Unknown error'}</div>;
  }

  return (
    <div className="llms-txt-react-app">
      <AdminBar />
      <div className="wrap">
        <div className="flex justify-center mb-6">
          <img 
            src={`${window.llmsTxtAdmin?.pluginUrl || ''}assets/react-build/assets/banner_upgrade.png`} 
            alt="LLMagnet AI SEO Optimizer" 
            className="max-w-full h-auto"
            onError={(e) => {
              // Fallback if the image doesn't load
              const target = e.target as HTMLImageElement;
              target.onerror = null;
              target.src = `${window.llmsTxtAdmin?.pluginUrl || ''}src/assets/images/banner_upgrade.png`;
              console.log('Trying fallback image path:', target.src);
            }}
          />
        </div>
        
        
        {!data.isWritable && (
          <div className="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-md">
            <p>WordPress root directory is not writable. LLMS.txt cannot be generated.</p>
          </div>
        )}
        
        <StatusBox
          rootPath={data.rootPath}
          isWritable={data.isWritable}
          lastGenerated={data.lastGenerated}
          onGenerateNow={handleGenerateNow}
        />
        
        <SettingsForm
          settings={data.settings}
          postTypes={data.postTypes}
          onSubmit={handleSaveSettings}
        />
      </div>
    </div>
  );
} 