import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Toast, ToastDescription, ToastProvider, ToastTitle, ToastViewport } from '@/components/ui/toast';

interface StatusBoxProps {
  rootPath: string;
  isWritable: boolean;
  lastGenerated: string | null;
  onGenerateNow: () => Promise<{success: boolean, message: string, timestamp?: string}>;
}

export function StatusBox({ rootPath, isWritable, lastGenerated, onGenerateNow }: StatusBoxProps) {
  const [isGenerating, setIsGenerating] = useState(false);
  const [toast, setToast] = useState<{open: boolean, title: string, description: string, variant: 'default' | 'destructive'}>({
    open: false,
    title: '',
    description: '',
    variant: 'default'
  });
  const [lastGeneratedTime, setLastGeneratedTime] = useState<string | null>(lastGenerated);

  const handleGenerateNow = async () => {
    setIsGenerating(true);
    
    try {
      const result = await onGenerateNow();
      
      if (result.success) {
        setToast({
          open: true,
          title: 'Success',
          description: result.message,
          variant: 'default'
        });
        
        if (result.timestamp) {
          setLastGeneratedTime(result.timestamp);
        }
      } else {
        setToast({
          open: true,
          title: 'Error',
          description: result.message,
          variant: 'destructive'
        });
      }
    } catch (error) {
      setToast({
        open: true,
        title: 'Error',
        description: 'Error generating LLMS.txt. Please check server permissions.',
        variant: 'destructive'
      });
    } finally {
      setIsGenerating(false);
    }
  };

  return (
    <>
      <div className="bg-white border border-gray-200 shadow-sm p-4 mb-6 rounded-md">
        <h2 className="text-lg font-medium mb-4">LLMS.txt Status</h2>
        <div className="space-y-2 mb-4">
          <p>
            <strong>Root Directory:</strong> {rootPath}
            {isWritable ? (
              <span className="ml-2 text-green-600 font-medium">(Writable)</span>
            ) : (
              <span className="ml-2 text-red-600 font-medium">(Not Writable)</span>
            )}
          </p>
          <p>
            <strong>Last Generated:</strong> {lastGeneratedTime || 'Never'}
          </p>
        </div>
        <div className="flex items-center">
          <Button 
            variant="wp-primary"
            disabled={!isWritable || isGenerating}
            onClick={handleGenerateNow}
          >
            {isGenerating ? 'Generating...' : 'Generate Now'}
          </Button>
          {isGenerating && (
            <div className="ml-3">
              <svg className="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
          )}
        </div>
      </div>

      <ToastProvider>
        <Toast 
          open={toast.open} 
          onOpenChange={(open) => setToast(prev => ({ ...prev, open }))}
          variant={toast.variant}
        >
          <div className="grid gap-1">
            <ToastTitle>{toast.title}</ToastTitle>
            <ToastDescription>{toast.description}</ToastDescription>
          </div>
        </Toast>
        <ToastViewport />
      </ToastProvider>
    </>
  );
} 